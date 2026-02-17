<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_log("Poster upload script started");

session_start();
require_once '../config.php';

header('Content-Type: application/json');

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    error_log("Poster upload failed: Unauthorized");
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log("Poster upload POST request received");
    
    $poster_name = trim($_POST['poster_name'] ?? '');
    $admin_id = $_SESSION['admin_id'] ?? null;
    
    error_log("Admin ID: " . ($admin_id ?? 'NULL'));
    error_log("Poster name: " . $poster_name);
    
    // Validate admin_id
    if (!$admin_id) {
        error_log("Poster upload failed: admin_id not set in session");
        echo json_encode(['success' => false, 'message' => 'Admin session invalid. Please log out and log in again.']);
        exit();
    }
    
    // Validate poster name
    if (empty($poster_name)) {
        error_log("Poster upload failed: Empty poster name");
        echo json_encode(['success' => false, 'message' => 'Poster name is required']);
        exit();
    }
    
    // Validate file upload
    if (!isset($_FILES['poster_file']) || $_FILES['poster_file']['error'] !== UPLOAD_ERR_OK) {
        $upload_error = isset($_FILES['poster_file']) ? $_FILES['poster_file']['error'] : 'No file';
        error_log("Poster upload failed: Upload error code " . $upload_error);
        echo json_encode(['success' => false, 'message' => 'No file uploaded or upload error (code: ' . $upload_error . ')']);
        exit();
    }
    
    $file = $_FILES['poster_file'];
    
    // Validate file type (PNG only for posters)
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    error_log("File type validated: " . $mime_type);
    
    if ($mime_type !== 'image/png') {
        error_log("Poster upload failed: Invalid file type: " . $mime_type);
        echo json_encode(['success' => false, 'message' => 'Only PNG files are allowed for posters']);
        exit();
    }
    
    // Validate file size (15MB max)
    if ($file['size'] > 15 * 1024 * 1024) {
        error_log("Poster upload failed: File too large: " . $file['size']);
        echo json_encode(['success' => false, 'message' => 'File size must be less than 15MB']);
        exit();
    }
    
    // Generate unique filename
    $timestamp = time();
    $unique_suffix = bin2hex(random_bytes(4));
    $filename = 'poster_' . $timestamp . '_' . $unique_suffix . '.png';
    $thumb_filename = 'thumb_' . $timestamp . '.jpg';
    
    // Define paths
    $upload_dir = '../uploads/posters/';
    $thumb_dir = '../uploads/posters/thumbs/';
    $upload_path = $upload_dir . $filename;
    $thumb_path = $thumb_dir . $thumb_filename;
    
    // Create directories if they don't exist
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    if (!is_dir($thumb_dir)) {
        mkdir($thumb_dir, 0755, true);
    }
    
    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
        error_log("Poster upload failed: Could not move uploaded file");
        echo json_encode(['success' => false, 'message' => 'Failed to save file']);
        exit();
    }
    
    // Generate thumbnail with transparency
    try {
        $source = imagecreatefrompng($upload_path);
        
        if ($source === false) {
            throw new Exception('Failed to create image from PNG');
        }
        
        // Get original dimensions
        $orig_width = imagesx($source);
        $orig_height = imagesy($source);
        
        // Calculate thumbnail dimensions (max 300px)
        $max_thumb_size = 300;
        if ($orig_width > $orig_height) {
            $new_width = $max_thumb_size;
            $new_height = (int)(($orig_height / $orig_width) * $max_thumb_size);
        } else {
            $new_height = $max_thumb_size;
            $new_width = (int)(($orig_width / $orig_height) * $max_thumb_size);
        }
        
        // Create thumbnail
        $thumbnail = imagecreatetruecolor($new_width, $new_height);
        
        // Preserve transparency
        $transparent = imagecolorallocatealpha($thumbnail, 255, 255, 255, 127);
        imagefill($thumbnail, 0, 0, $transparent);
        imagesavealpha($thumbnail, true);
        imagealphablending($thumbnail, false);
        
        // Resize
        imagecopyresampled($thumbnail, $source, 0, 0, 0, 0, $new_width, $new_height, $orig_width, $orig_height);
        
        // Save as JPEG for smaller size
        imagejpeg($thumbnail, $thumb_path, 85);
        imagedestroy($thumbnail);
        imagedestroy($source);
        
    } catch (Exception $e) {
        // Clean up uploaded file on error
        unlink($upload_path);
        error_log("Thumbnail creation failed: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Failed to create thumbnail: ' . $e->getMessage()]);
        exit();
    }
    
    // Generate unique ID for sharing
    $unique_id = uniqid('poster_', true);
    
    // Insert into database
    $stmt = $conn->prepare("INSERT INTO posters (unique_id, poster_name, poster_path, thumbnail_path, file_size, file_type, width, height, uploaded_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $rel_poster_path = 'uploads/posters/' . $filename;
    $rel_thumb_path = 'uploads/posters/thumbs/' . $thumb_filename;
    
    $stmt->bind_param("ssssissii", $unique_id, $poster_name, $rel_poster_path, $rel_thumb_path, $file['size'], $mime_type, $orig_width, $orig_height, $admin_id);
    
    error_log("Attempting database insert for poster: " . $poster_name);
    
    if ($stmt->execute()) {
        $poster_id = $stmt->insert_id;
        error_log("Poster upload successful! ID: " . $poster_id);
        echo json_encode([
            'success' => true, 
            'message' => 'Poster uploaded successfully',
            'poster_id' => $poster_id,
            'unique_id' => $unique_id,
            'redirect' => 'poster-define-slots.php?id=' . $poster_id
        ]);
    } else {
        error_log("Database insert failed: " . $conn->error);
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
