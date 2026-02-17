<?php
require_once '../config.php';

echo "<h2>Testing Posters Table Structure</h2>";

// Check if slot_count column exists
$result = $conn->query("DESCRIBE posters");
echo "<h3>Posters Table Columns:</h3><pre>";
while ($row = $result->fetch_assoc()) {
    echo $row['Field'] . " - " . $row['Type'] . "\print>";
}
echo "</pre>";

// Try to select with slot_count
echo "<h3>Test Query with slot_count subquery:</h3>";
$query = "SELECT p.*, 
          (SELECT COUNT(*) FROM poster_slots ps WHERE ps.poster_id = p.id) as slot_count
          FROM posters p LIMIT 1";

$result = $conn->query($query);
if ($result) {
    echo "<pre>SUCCESS - Query works!</pre>";
    if ($row = $result->fetch_assoc()) {
        echo "<pre>" . print_r($row, true) . "</pre>";
    }
} else {
    echo "<pre>ERROR: " . $conn->error . "</pre>";
}

// Check poster_slots table
echo "<h3>Poster Slots Table Data:</h3>";
$result = $conn->query("SELECT * FROM poster_slots ORDER BY poster_id, slot_number");
if ($result) {
    echo "<pre>";
    while ($row = $result->fetch_assoc()) {
        echo print_r($row, true) . "\n";
    }
    echo "</pre>";
} else {
    echo "<pre>ERROR: " . $conn->error . "</pre>";
}
?>
