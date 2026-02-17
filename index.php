<?php
// Smart Routing: Redirect based on frame count
require_once 'config.php';

// Count total available frames
$result = $conn->query("SELECT COUNT(*) as total FROM frames");

// Guard against query failure
if ($result === false) {
    http_response_code(500);
    error_log("Database query failed in index.php: " . $conn->error);
    include '404.php';
    exit();
}

$row = $result->fetch_assoc();
$totalFrames = (int)$row['total'];

// No frames - redirect to admin panel to add frames
if ($totalFrames === 0) {
    redirect('admin/');
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
    }
}

// Multiple frames - show gallery
redirect('gallery.php');
?>
