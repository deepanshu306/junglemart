<?php
require_once 'config.php';

// Create users table if it doesn't exist
$pdo->exec("
    CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(255) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL
    )
");

// Insert default admin account if it doesn't exist
$username = 'deepanshu2303';
$password = password_hash('Google@10666', PASSWORD_DEFAULT); // Hash the password

$stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
$stmt->execute([$username]);

if ($stmt->fetchColumn() == 0) {
    $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
    $stmt->execute([$username, $password]);
    echo "Default admin account created.";
} else {
    echo "Admin account already exists.";
}
?>
