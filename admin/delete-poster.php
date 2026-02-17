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
    $poster_id = intval($_POST['poster_id'] ?? 0);
    
    error_log("Delete poster request - Poster ID: $poster_id");
    
    if ($poster_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid poster ID']);
        exit();
    }
    
    // Get poster details
    $stmt = $conn->prepare("SELECT poster_path, thumbnail_path FROM posters WHERE id = ?");
    $stmt->bind_param("i", $poster_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Poster not found']);
        exit();
    }
    
    $poster = $result->fetch_assoc();
    $stmt->close();
    
    error_log("Found poster - Path: " . $poster['poster_path']);
    
    // Delete files
    $poster_file = '../' . $poster['poster_path'];
    $thumb_file = '../' . $poster['thumbnail_path'];
    
    if (file_exists($poster_file)) {
        if (unlink($poster_file)) {
            error_log("Deleted poster file: $poster_file");
        } else {
            error_log("Failed to delete poster file: $poster_file");
        }
    } else {
        error_log("Poster file not found: $poster_file");
    }
    
    if (file_exists($thumb_file)) {
        if (unlink($thumb_file)) {
            error_log("Deleted thumbnail file: $thumb_file");
        } else {
            error_log("Failed to delete thumbnail file: $thumb_file");
        }
    } else {
        error_log("Thumbnail file not found: $thumb_file");
    }
    
    // Delete from database (cascade should delete poster_slots and user_poster_photos)
    $stmt = $conn->prepare("DELETE FROM posters WHERE id = ?");
    $stmt->bind_param("i", $poster_id);
    
    if ($stmt->execute()) {
        error_log("Poster deleted from database successfully");
        echo json_encode(['success' => true, 'message' => 'Poster deleted successfully']);
    } else {
        error_log("Database error: " . $conn->error);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
    }
    
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
