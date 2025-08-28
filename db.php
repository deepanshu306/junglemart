<?php
// db.php
// Edit these credentials to match your MySQL setup
$DB_HOST = 'localhost';
$DB_NAME = 'junglemart';
$DB_USER = 'root';
$DB_PASS = ''; // <- set your DB password
$DB_CHAR = 'utf8mb4';

$dsn = "mysql:host={$DB_HOST};dbname={$DB_NAME};charset={$DB_CHAR}";
$options = [
  PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
  $pdo = new PDO($dsn, $DB_USER, $DB_PASS, $options);
} catch (PDOException $e) {
  // In production hide exact error; for dev it's useful
  echo "Database connection failed: " . htmlspecialchars($e->getMessage());
  exit;
}
