<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$frame_id = intval($_POST['frame_id'] ?? 0);
$frame_name = trim($_POST['frame_name'] ?? '');

if ($frame_id <= 0 || $frame_name === '') {
    echo json_encode(['success' => false, 'message' => 'A valid campaign name is required']);
    exit();
}

$stmt = $conn->prepare('UPDATE frames SET frame_name = ? WHERE id = ?');
$stmt->bind_param('si', $frame_name, $frame_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Campaign name updated successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}

$stmt->close();
?>