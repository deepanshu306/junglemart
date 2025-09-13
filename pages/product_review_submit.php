<?php
/** product_review_submit.php
 * Handles submission of product reviews.
 */

const DEV = true; // set to false on production

if (DEV) {
  ini_set('display_errors', '1');
  ini_set('display_startup_errors', '1');
  error_reporting(E_ALL);
}

$inc = __DIR__ . '/includes/db.php';
if (!file_exists($inc)) {
  http_response_code(500);
  exit('Missing file: includes/db.php');
}
require $inc;

if (!isset($pdo) || !($pdo instanceof PDO)) {
  http_response_code(500);
  exit('Database connection ($pdo) not initialized in includes/db.php');
}

// Validate POST inputs
$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$user_name = isset($_POST['user_name']) ? trim($_POST['user_name']) : '';
$rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
$comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';

if ($product_id <= 0 || $user_name === '' || $rating < 1 || $rating > 5 || $comment === '') {
  http_response_code(400);
  exit('Invalid input');
}

try {
  $stmt = $pdo->prepare("INSERT INTO reviews (product_id, user_name, rating, comment, created_at) VALUES (:product_id, :user_name, :rating, :comment, NOW())");
  $stmt->bindValue(':product_id', $product_id, PDO::PARAM_INT);
  $stmt->bindValue(':user_name', $user_name, PDO::PARAM_STR);
  $stmt->bindValue(':rating', $rating, PDO::PARAM_INT);
  $stmt->bindValue(':comment', $comment, PDO::PARAM_STR);
  $stmt->execute();
} catch (PDOException $e) {
  if (DEV) {
    exit('Database error: ' . $e->getMessage());
  }
  http_response_code(500);
  exit('Database error');
}

// Redirect back to product detail page
header('Location: product.php?id=' . $product_id . '#reviews');
exit;
?>
