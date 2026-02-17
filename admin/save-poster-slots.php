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
$slots_json = $_POST['slots'] ?? '';

error_log("Save poster slots - Poster ID: $poster_id");
error_log("Slots JSON: $slots_json");

if ($poster_id <= 0 || empty($slots_json)) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit();
}

$slots = json_decode($slots_json, true);

if (!is_array($slots) || empty($slots)) {
    echo json_encode(['success' => false, 'message' => 'Invalid slots data']);
    exit();
}

// Validate slot count (1-10)
if (count($slots) < 1 || count($slots) > 10) {
    echo json_encode(['success' => false, 'message' => 'Poster must have between 1 and 10 slots']);
    exit();
}

try {
    // Start transaction
    $conn->begin_transaction();
    
    error_log("Starting to save " . count($slots) . " slots for poster $poster_id");
    
    // Delete existing slots for this poster
    $stmt = $conn->prepare("DELETE FROM poster_slots WHERE poster_id = ?");
    $stmt->bind_param("i", $poster_id);
    $stmt->execute();
    $stmt->close();
    
    error_log("Deleted existing slots");
    
    // Insert new slots
    $stmt = $conn->prepare("INSERT INTO poster_slots (poster_id, slot_number, x_position, y_position, width, height, rotation) VALUES (?, ?, ?, ?, ?, ?, ?)");
    
    foreach ($slots as $slot) {
        $stmt->bind_param(
            "iiiiiii",
            $poster_id,
            $slot['slot_number'],
            $slot['x_position'],
            $slot['y_position'],
            $slot['width'],
            $slot['height'],
            $slot['rotation']
        );
        $stmt->execute();
        error_log("Inserted slot " . $slot['slot_number']);
    }
    
    $stmt->close();
    
    // Commit transaction
    $conn->commit();
    
    error_log("Successfully saved all slots");
    echo json_encode(['success' => true, 'message' => 'Slots saved successfully']);
    
} catch (Exception $e) {
    $conn->rollback();
    error_log("Error saving slots: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
