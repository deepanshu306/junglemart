<?php
session_start();
require_once 'config.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: admin_login.php');
    exit();
}

// Check if user is admin
if ($_SESSION['role'] !== 'admin') {
    die("Access denied. Admin privileges required.");
}

// Get counts for dashboard
$productCount = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$categoryCount = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
$inquiryCount = $pdo->query("SELECT COUNT(*) FROM inquiries")->fetchColumn();
$quotationCount = $pdo->query("SELECT COUNT(*) FROM quotations")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Jungle Mart</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body class="admin-panel">
    <?php include 'navbar.php'; ?>

    <div class="admin-container">
        <div class="admin-sidebar">
            <div class="admin-user-info">
                <h3>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></h3>
                <p>Role: <?php echo htmlspecialchars($_SESSION['role']); ?></p>
                <a href="logout.php" class="btn logout-btn">Logout</a>
            </div>
            
            <nav class="admin-menu">
                <a href="admin.php" class="menu-item active">Dashboard</a>
                <a href="manage_products.php" class="menu-item">Products</a>
                <a href="manage_categories.php" class="menu-item">Categories</a>
                <a href="manage_inquiries.php" class="menu-item">Inquiries</a>
                <a href="manage_quotations.php" class="menu-item">Quotations</a>
                <a href="manage_users.php" class="menu-item">Users</a>
            </nav>
        </div>

        <main class="admin-main">
            <section id="dashboard" class="dashboard-section">
                <h2>Dashboard Overview</h2>
                
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">📦</div>
                        <div class="stat-info">
                            <h3><?php echo $productCount; ?></h3>
                            <p>Total Products</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">📂</div>
                        <div class="stat-info">
                            <h3><?php echo $categoryCount; ?></h3>
                            <p>Categories</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">📧</div>
                        <div class="stat-info">
                            <h3><?php echo $inquiryCount; ?></h3>
                            <p>Inquiries</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">💰</div>
                        <div class="stat-info">
                            <h3><?php echo $quotationCount; ?></h3>
                            <p>Quotations</p>
                        </div>
                    </div>
                </div>

                <div class="quick-actions">
                    <h3>Quick Actions</h3>
                    <div class="action-buttons">
                        <a href="add_product.php" class="btn primary">Add New Product</a>
                        <a href="add_category.php" class="btn primary">Add New Category</a>
                        <a href="view_inquiries.php" class="btn secondary">View Inquiries</a>
                        <a href="view_quotations.php" class="btn secondary">View Quotations</a>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>
