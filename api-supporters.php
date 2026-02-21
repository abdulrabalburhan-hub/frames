<?php
// API endpoint to fetch campaign supporters for a specific frame
require_once 'config.php';

header('Content-Type: application/json');

// Get frame ID from request
$frame_id = isset($_GET['frame_id']) ? intval($_GET['frame_id']) : 0;
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 50; // Default to 50 supporters
$offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;

// Validate frame ID
if ($frame_id <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid frame ID'
    ]);
    exit();
}

try {
    // Verify frame exists
    $stmt = $conn->prepare("SELECT id, download_count FROM frames WHERE id = ?");
    $stmt->bind_param("i", $frame_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Frame not found'
        ]);
        exit();
    }
    
    $frame = $result->fetch_assoc();
    $stmt->close();
    
    // Fetch supporters (most recent first)
    $stmt = $conn->prepare("
        SELECT thumbnail_path, created_at 
        FROM frame_supporters 
        WHERE frame_id = ? 
        ORDER BY created_at DESC 
        LIMIT ? OFFSET ?
    ");
    $stmt->bind_param("iii", $frame_id, $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $supporters = [];
    while ($row = $result->fetch_assoc()) {
        $supporters[] = [
            'thumbnail' => $row['thumbnail_path'],
            'created_at' => $row['created_at']
        ];
    }
    $stmt->close();
    
    // Get total count of supporters
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM frame_supporters WHERE frame_id = ?");
    $stmt->bind_param("i", $frame_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $countRow = $result->fetch_assoc();
    $totalSupporters = $countRow['total'];
    $stmt->close();
    
    echo json_encode([
        'success' => true,
        'frame_id' => $frame_id,
        'download_count' => $frame['download_count'],
        'total_supporters' => $totalSupporters,
        'supporters' => $supporters,
        'limit' => $limit,
        'offset' => $offset,
        'has_more' => ($offset + $limit) < $totalSupporters
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching supporters: ' . $e->getMessage()
    ]);
}
?>
