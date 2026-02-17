<?php
// Generic Entry Point Handler
// NOTE: If you're using albn.org URL shortener, you typically don't need this file.
// Your short URLs at albn.org should point directly to frame.php?id=FRAME_ID
// This file is kept for generic entry points that don't specify a frame.

require_once 'config.php';

// Count total available frames
$result = $conn->query("SELECT COUNT(*) as total FROM frames");

if ($result === false) {
    http_response_code(500);
    exit();
}
$row = $result->fetch_assoc();
$totalFrames = (int)$row['total'];

// No frames available
if ($totalFrames === 0) {
    http_response_code(404);
    include '404.php';
    exit();
}

// Single frame - redirect directly to the frame editor
if ($totalFrames === 1) {
    $stmt = $conn->prepare("SELECT unique_id FROM frames LIMIT 1");
    $stmt->execute();
    $result = $stmt->get_result();
    $frame = $result->fetch_assoc();
    $stmt->close();
    
    if ($frame) {
        redirect('frame.php?id=' . $frame['unique_id']);
    } else {
        http_response_code(404);
        include '404.php';
        exit();
    }
}

// Multiple frames - show gallery for user to choose
redirect('gallery.php');
?>
