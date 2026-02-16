<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$frame_id = intval($_POST['frame_id'] ?? 0);
$slots_json = $_POST['slots'] ?? '';

if ($frame_id <= 0 || empty($slots_json)) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit();
}

$slots = json_decode($slots_json, true);

if (!is_array($slots) || empty($slots)) {
    echo json_encode(['success' => false, 'message' => 'Invalid slots data']);
    exit();
}

try {
    // Start transaction
    $conn->begin_transaction();
    
    // Update frame to mark as multi-photo
    $stmt = $conn->prepare("UPDATE frames SET is_multi_photo = 1, slot_count = ? WHERE id = ?");
    $slot_count = count($slots);
    $stmt->bind_param("ii", $slot_count, $frame_id);
    $stmt->execute();
    $stmt->close();
    
    // Delete existing slots for this frame
    $stmt = $conn->prepare("DELETE FROM frame_slots WHERE frame_id = ?");
    $stmt->bind_param("i", $frame_id);
    $stmt->execute();
    $stmt->close();
    
    // Insert new slots
    $stmt = $conn->prepare("INSERT INTO frame_slots (frame_id, slot_number, x_position, y_position, width, height, rotation) VALUES (?, ?, ?, ?, ?, ?, ?)");
    
    foreach ($slots as $slot) {
        $stmt->bind_param(
            "iiiiiii",
            $frame_id,
            $slot['slot_number'],
            $slot['x_position'],
            $slot['y_position'],
            $slot['width'],
            $slot['height'],
            $slot['rotation']
        );
        $stmt->execute();
    }
    
    $stmt->close();
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode(['success' => true, 'message' => 'Slots saved successfully']);
    
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
