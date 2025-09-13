<?php
require_once __DIR__ . '/../includes/db.php';

// Auth guard (use RELATIVE path so it works anywhere)
if (empty($_SESSION['admin_id'])) {
  header('Location: admin_login.php?err=Please+log+in');
  exit;
}

if (empty($page_title)) $page_title = 'Admin • Jungle Mart';

// active menu helper
$current = basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
function is_active($file){global $current; return $current===$file?' active':'';}

$adminName = $_SESSION['admin_name'] ?? 'Admin';

// No-cache for admin pages (optional)
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title><?= htmlspecialchars($page_title) ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="../css/admin.css?v=5">
</head>
<body class="admin-panel">
  <div class="admin-container">
    <aside class="admin-sidebar">
      <div class="admin-user-info">
        <h3><?= htmlspecialchars($adminName) ?></h3>
        <p class="muted">Administrator</p>
        <a class="logout-btn" href="logout.php">Logout</a>
      </div>
      <nav class="admin-menu">
        <a class="menu-item<?= is_active('admin_dashboard.php') ?>" href="admin_dashboard.php">Dashboard</a>
        <a class="menu-item<?= is_active('manage_products.php') ?>" href="manage_products.php">Manage Products</a>
        <a class="menu-item<?= is_active('add_product.php') ?>" href="add_product.php">Add Product</a>
        <a class="menu-item<?= is_active('manage_categories.php') ?>" href="manage_categories.php">Manage Categories</a>
        <a class="menu-item<?= is_active('orders.php') ?>" href="orders.php">Orders</a>
      </nav>
    </aside>
    <main class="admin-main">
