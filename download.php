<?php
// Force download of processed photos
require_once 'config.php';

// Get file path from query string
$file = $_GET['file'] ?? '';

// Security: only allow downloads from uploads/photos/
if (empty($file) || strpos($file, 'uploads/photos/') !== 0) {
    http_response_code(403);
    die('Invalid file path');
}

// Build full path
$filepath = __DIR__ . '/' . $file;

// Check if file exists
if (!file_exists($filepath)) {
    http_response_code(404);
    die('File not found');
}

// Get filename
$filename = basename($filepath);

// Set headers to force download
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . filesize($filepath));
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Output file
readfile($filepath);
exit();
?>
