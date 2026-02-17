<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors (we'll return them as JSON)
ini_set('log_errors', 1);

session_start();
require_once '../config.php';

header('Content-Type: application/json');

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $frame_name = trim($_POST['frame_name'] ?? '');
    $admin_id = $_SESSION['admin_id'];
    
    // Validate frame name
    if (empty($frame_name)) {
        echo json_encode(['success' => false, 'message' => 'Frame name is required']);
        exit();
    }
    
    // Validate file upload
    if (!isset($_FILES['frame_file']) || $_FILES['frame_file']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => 'No file uploaded or upload error']);
        exit();
    }
    
    $file = $_FILES['frame_file'];
    
    // Validate file type
    $allowed_types = ['image/png', 'image/jpeg', 'image/jpg'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mime_type, $allowed_types)) {
        echo json_encode(['success' => false, 'message' => 'Only PNG and JPG files are allowed']);
        exit();
    }
    
    // Validate file size (15MB)
    if ($file['size'] > MAX_UPLOAD_SIZE) {
        echo json_encode(['success' => false, 'message' => 'File size must be less than 15MB']);
        exit();
    }
    
    // Generate unique filename
    $timestamp = time();
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'frame_' . $timestamp . '.' . $extension;
    $thumb_filename = 'thumb_' . $timestamp . '.jpg';
    
    // Define paths
    $upload_dir = '../uploads/frames/';
    $thumb_dir = '../uploads/frames/thumbs/';
    $upload_path = $upload_dir . $filename;
    $thumb_path = $thumb_dir . $thumb_filename;
    
    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
        echo json_encode(['success' => false, 'message' => 'Failed to save file']);
        exit();
    }
    
    // Generate thumbnail
    try {
        $thumbnail_created = false;
        
        // Determine image type and create image resource
        if ($mime_type === 'image/png') {
            $source = imagecreatefrompng($upload_path);
        } else {
            $source = imagecreatefromjpeg($upload_path);
        }
        
        if ($source !== false) {
            // Get original dimensions
            $orig_width = imagesx($source);
            $orig_height = imagesy($source);
            
            // Calculate thumbnail dimensions (300px max width)
            $thumb_width = 300;
            $thumb_height = (int)(($orig_height / $orig_width) * $thumb_width);
            
            // Create thumbnail
            $thumbnail = imagecreatetruecolor($thumb_width, $thumb_height);
            
            // Preserve transparency for PNG
            if ($mime_type === 'image/png') {
                imagealphablending($thumbnail, false);
                imagesavealpha($thumbnail, true);
                $transparent = imagecolorallocatealpha($thumbnail, 255, 255, 255, 127);
                imagefilledrectangle($thumbnail, 0, 0, $thumb_width, $thumb_height, $transparent);
            }
            
            // Resize image
            imagecopyresampled($thumbnail, $source, 0, 0, 0, 0, $thumb_width, $thumb_height, $orig_width, $orig_height);
            
            // Save thumbnail as JPEG
            imagejpeg($thumbnail, $thumb_path, 90);
            
            // Free memory
            imagedestroy($source);
            imagedestroy($thumbnail);
            
            $thumbnail_created = true;
        }
        
        if (!$thumbnail_created) {
            // If thumbnail creation failed, delete uploaded file
            unlink($upload_path);
            echo json_encode(['success' => false, 'message' => 'Failed to create thumbnail']);
            exit();
        }
        
    } catch (Exception $e) {
        // Clean up on error
        if (file_exists($upload_path)) unlink($upload_path);
        if (file_exists($thumb_path)) unlink($thumb_path);
        echo json_encode(['success' => false, 'message' => 'Error processing image: ' . $e->getMessage()]);
        exit();
    }
    
    // Generate unique ID for sharing
    $unique_id = uniqid('frame_', true);
    
    // Insert into database
    $stmt = $conn->prepare("INSERT INTO frames (unique_id, frame_name, frame_path, thumbnail_path, file_size, file_type, uploaded_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $frame_path = 'uploads/frames/' . $filename;
    $thumbnail_path = 'uploads/frames/thumbs/' . $thumb_filename;
    $file_size = (int)$file['size'];
    $stmt->bind_param("ssssisi", $unique_id, $frame_name, $frame_path, $thumbnail_path, $file_size, $mime_type, $admin_id);
    
    if ($stmt->execute()) {
        $share_url = SITE_URL . '/frame.php?id=' . $unique_id;
        echo json_encode([
            'success' => true, 
            'message' => 'Frame uploaded successfully!',
            'share_url' => $share_url
        ]);
    } else {
        // Clean up files on database error
        unlink($upload_path);
        unlink($thumb_path);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
    }
    
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
