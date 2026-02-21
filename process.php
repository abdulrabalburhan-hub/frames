<?php
require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

// Get POST data
$frame_id = intval($_POST['frame_id'] ?? 0);
$is_multi_photo = isset($_POST['is_multi_photo']) && $_POST['is_multi_photo'] == '1';

// Validate inputs
if ($frame_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid frame ID']);
    exit();
}

// Fetch frame details
$stmt = $conn->prepare("SELECT * FROM frames WHERE id = ?");
$stmt->bind_param("i", $frame_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Frame not found']);
    exit();
}

$frame = $result->fetch_assoc();
$stmt->close();

try {
    // Load frame image
    $frame_path = __DIR__ . '/' . $frame['frame_path'];
    
    if (!file_exists($frame_path)) {
        throw new Exception('Frame file not found: ' . $frame_path);
    }
    
    $frame_mime = $frame['file_type'];
    $frame_img = ($frame_mime === 'image/png') 
        ? imagecreatefrompng($frame_path) 
        : imagecreatefromjpeg($frame_path);
    
    if ($frame_img === false) {
        throw new Exception('Failed to load frame image');
    }
    
    $frame_width = imagesx($frame_img);
    $frame_height = imagesy($frame_img);
    
    // Create composite image
    $composite = imagecreatetruecolor($frame_width, $frame_height);
    $white = imagecolorallocate($composite, 255, 255, 255);
    imagefilledrectangle($composite, 0, 0, $frame_width, $frame_height, $white);
    
    if ($is_multi_photo) {
        // Multi-photo processing
        $slot_photos_json = $_POST['slot_photos'] ?? '';
        
        if (empty($slot_photos_json)) {
            throw new Exception('No slot photos provided');
        }
        
        $slot_photos = json_decode($slot_photos_json, true);
        
        if (!is_array($slot_photos) || empty($slot_photos)) {
            throw new Exception('Invalid slot photos data');
        }
        
        // Fetch slot definitions
        $stmt = $conn->prepare("SELECT * FROM frame_slots WHERE frame_id = ? ORDER BY slot_number ASC");
        $stmt->bind_param("i", $frame_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $slots = [];
        while ($row = $result->fetch_assoc()) {
            $slots[$row['slot_number']] = $row;
        }
        $stmt->close();
        
        // Process each slot
        foreach ($slot_photos as $slotNum => $photoData) {
            if (!isset($slots[$slotNum])) continue;
            
            $slot = $slots[$slotNum];
            
            // Decode photo data
            $photo_base64 = str_replace('data:image/png;base64,', '', $photoData['photo_data']);
            $photo_base64 = str_replace(' ', '+', $photo_base64);
            $decoded_photo = base64_decode($photo_base64);
            
            if ($decoded_photo === false) continue;
            
            $photo = imagecreatefromstring($decoded_photo);
            if ($photo === false) continue;
            
            // Get transformation data
            $scale = floatval($photoData['scale'] ?? 1.0);
            $rotation = intval($photoData['rotation'] ?? 0);
            $fineRotation = intval($photoData['fineRotation'] ?? 0);
            $offsetX = intval($photoData['x'] ?? 0);
            $offsetY = intval($photoData['y'] ?? 0);
            
            // Apply photo to slot
            compositePhotoInSlot($composite, $photo, $slot, $scale, $rotation, $fineRotation, $offsetX, $offsetY);
            
            imagedestroy($photo);
        }
        
    } else {
        // Single photo processing
        $photo_data = $_POST['photo_data'] ?? '';
        
        if (empty($photo_data)) {
            throw new Exception('No photo data provided');
        }
        
        $photo_data = str_replace('data:image/png;base64,', '', $photo_data);
        $photo_data = str_replace(' ', '+', $photo_data);
        $decoded_photo = base64_decode($photo_data);
        
        if ($decoded_photo === false) {
            throw new Exception('Failed to decode image data');
        }
        
        $photo = imagecreatefromstring($decoded_photo);
        
        if ($photo === false) {
            throw new Exception('Failed to create image from data');
        }
        
        $photo_width = imagesx($photo);
        $photo_height = imagesy($photo);
        
        $scale = min($frame_width / $photo_width, $frame_height / $photo_height);
        $scaled_width = (int)($photo_width * $scale);
        $scaled_height = (int)($photo_height * $scale);
        
        $x_offset = (int)(($frame_width - $scaled_width) / 2);
        $y_offset = (int)(($frame_height - $scaled_height) / 2);
        
        imagecopyresampled(
            $composite, $photo,
            $x_offset, $y_offset, 0, 0,
            $scaled_width, $scaled_height,
            $photo_width, $photo_height
        );
        
        imagedestroy($photo);
    }
    
    // Overlay frame on top
    imagealphablending($composite, true);
    imagesavealpha($composite, true);
    imagecopy($composite, $frame_img, 0, 0, 0, 0, $frame_width, $frame_height);
    
    // Generate output
    $timestamp = time();
    $filename = 'framed_photo_' . $timestamp . '.jpg';
    
    ob_start();
    imagejpeg($composite, null, 85);
    $imageData = ob_get_clean();
    
    // Save supporter thumbnail (storage-optimized)
    $thumbnailSaved = saveSupporterThumbnail($composite, $frame_id);
    
    // Increment download count for this frame
    incrementDownloadCount($frame_id);
    
    imagedestroy($frame_img);
    imagedestroy($composite);
    
    echo json_encode([
        'success' => true,
        'message' => 'Photo processed successfully',
        'image_data' => base64_encode($imageData),
        'filename' => $filename,
        'supporter_saved' => $thumbnailSaved
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error processing image: ' . $e->getMessage()
    ]);
}

/**
 * Save a thumbnail of the supporter's framed photo
 * Optimized for storage: 120x120px, JPEG quality 75
 */
function saveSupporterThumbnail($composite, $frame_id) {
    global $conn;
    
    try {
        // Create thumbnail directory if it doesn't exist
        $thumbDir = __DIR__ . '/uploads/supporters/thumbs';
        if (!file_exists($thumbDir)) {
            mkdir($thumbDir, 0755, true);
        }
        
        // Get original dimensions
        $originalWidth = imagesx($composite);
        $originalHeight = imagesy($composite);
        
        // Calculate thumbnail size (120x120 maintains visibility while minimizing storage)
        $thumbSize = 120;
        $thumbWidth = $thumbSize;
        $thumbHeight = $thumbSize;
        
        // Determine crop dimensions to maintain aspect ratio
        if ($originalWidth > $originalHeight) {
            $sourceSize = $originalHeight;
            $sourceX = ($originalWidth - $originalHeight) / 2;
            $sourceY = 0;
        } else {
            $sourceSize = $originalWidth;
            $sourceX = 0;
            $sourceY = ($originalHeight - $originalWidth) / 2;
        }
        
        // Create thumbnail image
        $thumbnail = imagecreatetruecolor($thumbWidth, $thumbHeight);
        $white = imagecolorallocate($thumbnail, 255, 255, 255);
        imagefill($thumbnail, 0, 0, $white);
        
        // Resample to thumbnail size (square crop from center)
        imagecopyresampled(
            $thumbnail, $composite,
            0, 0, $sourceX, $sourceY,
            $thumbWidth, $thumbHeight, $sourceSize, $sourceSize
        );
        
        // Generate unique filename
        $timestamp = time();
        $random = mt_rand(1000, 9999);
        $filename = 'supporter_' . $frame_id . '_' . $timestamp . '_' . $random . '.jpg';
        $filepath = $thumbDir . '/' . $filename;
        $relativePath = 'uploads/supporters/thumbs/' . $filename;
        
        // Save with optimized quality (75 provides good balance)
        imagejpeg($thumbnail, $filepath, 75);
        $fileSize = filesize($filepath);
        
        imagedestroy($thumbnail);
        
        // Save to database
        $stmt = $conn->prepare("INSERT INTO frame_supporters (frame_id, thumbnail_path, thumbnail_size) VALUES (?, ?, ?)");
        $stmt->bind_param("isi", $frame_id, $relativePath, $fileSize);
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
        
    } catch (Exception $e) {
        error_log("Error saving supporter thumbnail: " . $e->getMessage());
        return false;
    }
}

/**
 * Increment the download count for a frame
 */
function incrementDownloadCount($frame_id) {
    global $conn;
    
    try {
        $stmt = $conn->prepare("UPDATE frames SET download_count = download_count + 1 WHERE id = ?");
        $stmt->bind_param("i", $frame_id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    } catch (Exception $e) {
        error_log("Error incrementing download count: " . $e->getMessage());
        return false;
    }
}

/**
 * Composite a photo into a slot with transformations
 */
function compositePhotoInSlot($composite, $photo, $slot, $scale, $rotation, $fineRotation, $offsetX, $offsetY) {
    $slotWidth = intval($slot['width']);
    $slotHeight = intval($slot['height']);
    $slotX = intval($slot['x_position']);
    $slotY = intval($slot['y_position']);
    $slotRotation = intval($slot['rotation']);
    
    // Create slot canvas
    $slotCanvas = imagecreatetruecolor($slotWidth, $slotHeight);
    imagealphablending($slotCanvas, false);
    $transparent = imagecolorallocatealpha($slotCanvas, 0, 0, 0, 127);
    imagefill($slotCanvas, 0, 0, $transparent);
    imagesavealpha($slotCanvas, true);
    
    // Rotate photo if needed (combine base rotation + fine rotation)
    $totalRotation = $rotation + $fineRotation;
    $rotatedPhoto = $photo;
    if ($totalRotation != 0) {
        $rotatedPhoto = imagerotate($photo, -$totalRotation, $transparent);
        imagealphablending($rotatedPhoto, false);
        imagesavealpha($rotatedPhoto, true);
    }
    
    // Calculate scaled dimensions
    $photoWidth = imagesx($rotatedPhoto);
    $photoHeight = imagesy($rotatedPhoto);
    $scaledWidth = (int)($photoWidth * $scale);
    $scaledHeight = (int)($photoHeight * $scale);
    
    // Calculate position (centered + offset)
    $photoX = ($slotWidth - $scaledWidth) / 2 + $offsetX;
    $photoY = ($slotHeight - $scaledHeight) / 2 + $offsetY;
    
    // Draw photo on slot canvas
    imagealphablending($slotCanvas, true);
    imagecopyresampled(
        $slotCanvas, $rotatedPhoto,
        $photoX, $photoY, 0, 0,
        $scaledWidth, $scaledHeight,
        $photoWidth, $photoHeight
    );
    
    if ($totalRotation != 0 && $rotatedPhoto != $photo) {
        imagedestroy($rotatedPhoto);
    }
    
    // Copy slot canvas to composite at slot position
    imagealphablending($composite, true);
    imagecopy(
        $composite, $slotCanvas,
        $slotX - $slotWidth / 2,
        $slotY - $slotHeight / 2,
        0, 0,
        $slotWidth, $slotHeight
    );
    
    imagedestroy($slotCanvas);
}
?>
