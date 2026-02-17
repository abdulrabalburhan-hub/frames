<?php
// Test database connection
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Testing Database Connection</h2>";

require_once '../config.php';

echo "✓ Config loaded<br>";
echo "Database: " . DB_NAME . "<br>";
echo "Host: " . DB_HOST . "<br>";
echo "User: " . DB_USER . "<br><br>";

if ($conn) {
    echo "✓ Database connected successfully!<br><br>";
    
    // Check admin_users table
    $result = $conn->query("SELECT * FROM admin_users");
    if ($result) {
        echo "✓ admin_users table exists<br>";
        echo "Admin users found: " . $result->num_rows . "<br><br>";
        
        while ($row = $result->fetch_assoc()) {
            echo "Username: " . $row['username'] . "<br>";
            echo "Password hash: " . substr($row['password'], 0, 20) . "...<br>";
            
            // Test password
            if (password_verify('Admin@123', $row['password'])) {
                echo "✓ Password 'Admin@123' is CORRECT for this user<br>";
            } else {
                echo "✗ Password 'Admin@123' does NOT match<br>";
                echo "<br><strong>Fix this by running in phpMyAdmin:</strong><br>";
                $newHash = password_hash('Admin@123', PASSWORD_BCRYPT);
                echo "<code>UPDATE admin_users SET password = '$newHash' WHERE username = 'admin';</code>";
            }
        }
    }
} else {
    echo "✗ Database connection failed!<br>";
    echo "Error: " . mysqli_connect_error();
}
?>
