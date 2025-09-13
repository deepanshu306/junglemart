<?php
// /junglemart/admin/product_toggle.php
require_once __DIR__ . '/includes/db.php';

// Auth guard
if (empty($_SESSION['admin_id'])) {
  header('Location: /admin_login.php?err=Please+log+in'); exit;
}

// CSRF check
if (empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) {
  $_SESSION['flash_err'] = 'Session expired. Please try again.';
  header('Location: /manage_products.php'); exit;
}

$id = (int)($_POST['id'] ?? 0);
$to = (int)($_POST['to'] ?? 0);


if ($id <= 0) {
  $_SESSION['flash_err'] = 'Invalid product ID.';
  header('Location: /manage_products.php'); exit;
}

try {
  $stmt = $pdo->prepare("UPDATE products SET is_active = ? WHERE id = ?");
  $stmt->execute([$to, $id]);
  $_SESSION['flash_success'] = 'Product ' . ($to ? 'activated' : 'deactivated') . ' successfully.';
} catch (Exception $e) {
  $_SESSION['flash_err'] = 'Failed to update product status.';
}

header('Location: /manage_products.php'); exit;
