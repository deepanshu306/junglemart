<?php
// Start session (once, before any output)
if (session_status() === PHP_SESSION_NONE) {
    $cookieParams = session_get_cookie_params();
    $isSecure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
    // Allow secure to be false on localhost HTTP for session cookie to work
    if (isset($_SERVER['HTTP_HOST']) && (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false || strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false)) {
      $isSecure = false;
    }
    session_set_cookie_params([
      'lifetime' => $cookieParams['lifetime'],
      'path' => $cookieParams['path'],
      'domain' => $cookieParams['domain'],
      'secure' => $isSecure,
      'httponly' => true,
      'samesite' => 'Lax'
    ]);
    session_start();
}

$DB_HOST = 'localhost';
$DB_NAME = 'junglemart';
$DB_USER = 'junglemart2025';
$DB_PASS = 'Junglemart@2025';
$DB_CHAR = 'utf8mb4';

$dsn = "mysql:host={$DB_HOST};dbname={$DB_NAME};charset={$DB_CHAR}";
$options = [
  PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
  $pdo = new PDO($dsn, $DB_USER, $DB_PASS, $options);

    
  // Create inquiries table if it doesn't exist
  $pdo->exec("
    CREATE TABLE IF NOT EXISTS inquiries (
      id INT AUTO_INCREMENT PRIMARY KEY,
      name VARCHAR(255) NOT NULL,
      email VARCHAR(255) NOT NULL,
      message TEXT NOT NULL,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
  ");
  
  // Create quotations table if it doesn't exist
  $pdo->exec("
    CREATE TABLE IF NOT EXISTS quotations (
      id INT AUTO_INCREMENT PRIMARY KEY,
      customer_name VARCHAR(255) NOT NULL,
      customer_email VARCHAR(255) NOT NULL,
      customer_phone VARCHAR(50) NOT NULL,
      notes TEXT,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
  ");

  // Create quotation_items table if it doesn't exist
  $pdo->exec("
    CREATE TABLE IF NOT EXISTS quotation_items (
      id INT AUTO_INCREMENT PRIMARY KEY,
      quotation_id INT NOT NULL,
      product_id VARCHAR(255),
      product_name VARCHAR(255),
      qty INT NOT NULL DEFAULT 1,
      price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
      wholesale_price DECIMAL(10,2) DEFAULT NULL,
      moq INT DEFAULT 1,
      FOREIGN KEY (quotation_id) REFERENCES quotations(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
  ");
  
} catch (PDOException $e) {
  die('Database connection failed: ' . htmlspecialchars($e->getMessage()));
}
