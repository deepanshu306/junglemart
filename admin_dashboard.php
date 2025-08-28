<?php
session_start();
require_once 'config.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: admin_login.php');
    exit();
}

// Fetch products and categories from the database
$products = $pdo->query("SELECT * FROM products")->fetchAll();
$categories = $pdo->query("SELECT * FROM categories")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Jungle Mart</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <h1>Admin Dashboard</h1>
    <h2>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></h2>
    
    <h3>Products</h3>
    <ul>
        <?php foreach ($products as $product): ?>
            <li><?php echo htmlspecialchars($product['name']); ?></li>
        <?php endforeach; ?>
    </ul>

    <h3>Categories</h3>
    <ul>
        <?php foreach ($categories as $category): ?>
            <li><?php echo htmlspecialchars($category['name']); ?></li>
        <?php endforeach; ?>
    </ul>

    <a href="admin.php">Back to Admin Panel</a>
    <a href="logout.php">Logout</a>
</body>
</html>
