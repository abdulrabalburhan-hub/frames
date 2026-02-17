<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$poster_id = intval($_POST['poster_id'] ?? 0);

error_log("Auto-detect slots - Poster ID: $poster_id");

if ($poster_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid poster ID']);
    exit();
}

// Get poster details
$stmt = $conn->prepare("SELECT poster_path FROM posters WHERE id = ?");
$stmt->bind_param("i", $poster_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Poster not found']);
    exit();
}

$poster = $result->fetch_assoc();
$stmt->close();

$poster_file = '../' . $poster['poster_path'];

error_log("Auto-detecting slots for: $poster_file");

if (!file_exists($poster_file)) {
    error_log("Poster file not found: $poster_file");
    echo json_encode(['success' => false, 'message' => 'Poster file not found']);
    exit();
}

try {
    // Load image
    $image = imagecreatefrompng($poster_file);
    if (!$image) {
        throw new Exception('Failed to load poster image');
    }
    
    $width = imagesx($image);
    $height = imagesy($image);
    
    error_log("Image dimensions: {$width}x{$height}");
    
    // Find transparent regions (potential photo slots)
    $slots = [];
    $visited = [];
    
    // Initialize visited array
    for ($y = 0; $y < $height; $y++) {
        $visited[$y] = array_fill(0, $width, false);
    }
    
    // Scan image for transparent regions
    $slot_number = 1;
    for ($y = 0; $y < $height; $y += 10) { // Sample every 10 pixels for performance
        for ($x = 0; $x < $width; $x += 10) {
            if ($visited[$y][$x]) continue;
            
            $rgba = imagecolorat($image, $x, $y);
            $alpha = ($rgba & 0x7F000000) >> 24;
            
            // If pixel is transparent (alpha > 100)
            if ($alpha > 100) {
                // Find bounding box of this transparent region
                $region = floodFillBounds($image, $x, $y, $width, $height, $visited);
                
                if ($region && $region['width'] > 50 && $region['height'] > 50) {
                    $slots[] = [
                        'slot_number' => $slot_number++,
                        'x_position' => $region['centerX'],
                        'y_position' => $region['centerY'],
                        'width' => $region['width'],
                        'height' => $region['height'],
                        'rotation' => 0
                    ];
                    
                    error_log("Detected slot: " . json_encode($region));
                    
                    if (count($slots) >= 10) break 2; // Max 10 slots
                }
            }
        }
    }
    
    imagedestroy($image);
    
    error_log("Total slots detected: " . count($slots));
    
    if (empty($slots)) {
        echo json_encode(['success' => false, 'message' => 'No photo slots detected. Please draw them manually.']);
    } else {
        echo json_encode(['success' => true, 'slots' => $slots, 'message' => 'Detected ' . count($slots) . ' slots']);
    }
    
} catch (Exception $e) {
    error_log("Auto-detect error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

function floodFillBounds($image, $startX, $startY, $width, $height, &$visited) {
    $minX = $startX;
    $maxX = $startX;
    $minY = $startY;
    $maxY = $startY;
    
    $queue = [[$startX, $startY]];
    $visited[$startY][$startX] = true;
    $pixelCount = 0;
    
    while (!empty($queue) && $pixelCount < 10000) { // Limit iterations
        list($x, $y) = array_shift($queue);
        $pixelCount++;
        
        $minX = min($minX, $x);
        $maxX = max($maxX, $x);
        $minY = min($minY, $y);
        $maxY = max($maxY, $y);
        
        // Check 4 directions (with step size for performance)
        $directions = [[0, -5], [0, 5], [-5, 0], [5, 0]];
        foreach ($directions as list($dx, $dy)) {
            $newX = $x + $dx;
            $newY = $y + $dy;
            
            if ($newX >= 0 && $newX < $width && $newY >= 0 && $newY < $height && !$visited[$newY][$newX]) {
                $rgba = imagecolorat($image, $newX, $newY);
                $alpha = ($rgba & 0x7F000000) >> 24;
                
                if ($alpha > 100) { // Transparent
                    $visited[$newY][$newX] = true;
                    $queue[] = [$newX, $newY];
                }
            }
        }
    }
    
    $regionWidth = $maxX - $minX;
    $regionHeight = $maxY - $minY;
    
    if ($regionWidth < 30 || $regionHeight < 30) {
        return null; // Too small
    }
    
    return [
        'minX' => $minX,
        'maxX' => $maxX,
        'minY' => $minY,
        'maxY' => $maxY,
        'width' => $regionWidth,
        'height' => $regionHeight,
        'centerX' => (int)(($minX + $maxX) / 2),
        'centerY' => (int)(($minY + $maxY) / 2)
    ];
}
?>
