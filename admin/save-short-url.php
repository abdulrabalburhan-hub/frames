<?php
// Save short URL for a frame
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
    $short_url = trim($_POST['short_url'] ?? '');
    
    if ($frame_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid frame ID']);
        exit();
    }
    
    // Validate short URL format (optional - remove if you want more flexibility)
    if (!empty($short_url) && !filter_var($short_url, FILTER_VALIDATE_URL)) {
        // If it's not a full URL, check if it's a valid format like "albn.org/..."
        if (!preg_match('/^[a-zA-Z0-9\.\-\/]+$/', $short_url)) {
            echo json_encode(['success' => false, 'message' => 'Invalid short URL format']);
            exit();
        }
    }
    
    // Allow empty string to clear the short URL
    $stmt = $conn->prepare("UPDATE frames SET short_url = ? WHERE id = ?");
    $stmt->bind_param("si", $short_url, $frame_id);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Short URL saved successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to save short URL: ' . $conn->error
        ]);
    }
    
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
