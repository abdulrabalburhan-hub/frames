<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $frame_id = intval($_POST['frame_id'] ?? 0);
    
    if ($frame_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid frame ID']);
        exit();
    }
    
    // Get frame details
    $stmt = $conn->prepare("SELECT frame_path, thumbnail_path FROM frames WHERE id = ?");
    $stmt->bind_param("i", $frame_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Frame not found']);
        exit();
    }
    
    $frame = $result->fetch_assoc();
    $stmt->close();
    
    // Delete files
    $frame_file = '../' . $frame['frame_path'];
    $thumb_file = '../' . $frame['thumbnail_path'];
    
    if (file_exists($frame_file)) {
        unlink($frame_file);
    }
    
    if (file_exists($thumb_file)) {
        unlink($thumb_file);
    }
    
    // Delete from database (cascade will delete related user_photos)
    $stmt = $conn->prepare("DELETE FROM frames WHERE id = ?");
    $stmt->bind_param("i", $frame_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Frame deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
    }
    
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
