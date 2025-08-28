<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'junglemart');
define('DB_USER', 'root');
define('DB_PASS', '');

// Site Configuration
define('SITE_NAME', 'Jungle Mart');
define('SITE_URL', 'http://localhost/junglemart');
define('ADMIN_EMAIL', 'admin@junglemart.com');

// File Upload Configuration
define('UPLOAD_PATH', 'uploads/');
define('MAX_FILE_SIZE', 5000000); // 5MB

// Pagination
define('ITEMS_PER_PAGE', 12);

// Start Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database Connection
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Helper Functions
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function redirect($url) {
    header("Location: " . $url);
    exit();
}

function formatPrice($price) {
    return "₹" . number_format($price, 2);
}

// Check if database tables exist
function checkDatabaseTables($pdo) {
    $requiredTables = ['users', 'products', 'categories'];
    $existingTables = [];
    
    try {
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        return $tables;
    } catch(PDOException $e) {
        return [];
    }
}
?>
