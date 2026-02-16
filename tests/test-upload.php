<?php
// Test upload functionality
session_start();
require_once '../config.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Testing Upload Configuration</h2>";

// Check if logged in
if (!isset($_SESSION['admin_logged_in'])) {
    echo "❌ Not logged in<br>";
} else {
    echo "✓ Logged in as: " . $_SESSION['admin_username'] . "<br>";
}

// Check directories
$dirs = [
    '../uploads/frames/' => 'Frame uploads directory',
    '../uploads/frames/thumbs/' => 'Thumbnails directory',
    '../uploads/photos/' => 'Photos directory'
];

foreach ($dirs as $dir => $label) {
    if (is_dir($dir)) {
        echo "✓ $label exists<br>";
        if (is_writable($dir)) {
            echo "  ✓ Writable<br>";
        } else {
            echo "  ❌ NOT writable<br>";
        }
    } else {
        echo "❌ $label does NOT exist<br>";
    }
}

// Check GD library
if (extension_loaded('gd')) {
    echo "✓ GD library loaded<br>";
    $gdInfo = gd_info();
    echo "  - PNG Support: " . ($gdInfo['PNG Support'] ? 'Yes' : 'No') . "<br>";
    echo "  - JPEG Support: " . ($gdInfo['JPEG Support'] ? 'Yes' : 'No') . "<br>";
} else {
    echo "❌ GD library NOT loaded<br>";
}

// Check upload limits
echo "<br><strong>PHP Upload Limits:</strong><br>";
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "<br>";
echo "post_max_size: " . ini_get('post_max_size') . "<br>";
echo "max_file_uploads: " . ini_get('max_file_uploads') . "<br>";
echo "memory_limit: " . ini_get('memory_limit') . "<br>";

// Check database
echo "<br><strong>Database Check:</strong><br>";
$result = $conn->query("DESCRIBE frames");
if ($result) {
    echo "✓ 'frames' table exists<br>";
    echo "<strong>Columns:</strong><br>";
    while ($row = $result->fetch_assoc()) {
        echo "  - {$row['Field']} ({$row['Type']})<br>";
    }
} else {
    echo "❌ Cannot access 'frames' table: " . $conn->error . "<br>";
}
?>
