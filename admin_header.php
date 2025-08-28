<header class="admin-header">
    <div class="header-content">
        <h1>🌿 Jungle Mart Admin Panel</h1>
        <div class="user-info">
            <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
            <a href="logout.php" class="btn logout-btn">Logout</a>
        </div>
    </div>
</header>

<nav class="admin-nav">
    <ul>
        <li><a href="admin.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'admin.php' ? 'active' : ''; ?>">Dashboard</a></li>
        <li><a href="manage_products.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'manage_products.php' ? 'active' : ''; ?>">Products</a></li>
        <li><a href="manage_categories.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'manage_categories.php' ? 'active' : ''; ?>">Categories</a></li>
        <li><a href="manage_inquiries.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'manage_inquiries.php' ? 'active' : ''; ?>">Inquiries</a></li>
        <li><a href="manage_quotations.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'manage_quotations.php' ? 'active' : ''; ?>">Quotations</a></li>
        <li><a href="manage_users.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'manage_users.php' ? 'active' : ''; ?>">Users</a></li>
    </ul>
</nav>
