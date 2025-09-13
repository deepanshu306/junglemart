<?php
session_start();
require_once '../includes/db.php';

// Get selected category from URL
$selectedCategoryId = isset($_GET['id']) ? (int)$_GET['id'] : null;
$selectedCategory = null;
$products = [];

// Fetch all main categories (parent_id IS NULL)
$stmt = $pdo->prepare("SELECT * FROM categories WHERE parent_id IS NULL ORDER BY name ASC");
$stmt->execute();
$mainCategories = $stmt->fetchAll();

// Fetch subcategories for each main category
$subcategories = [];
foreach ($mainCategories as $mainCat) {
    $stmtSub = $pdo->prepare("SELECT * FROM categories WHERE parent_id = ? ORDER BY name ASC");
    $stmtSub->execute([$mainCat['id']]);
    $subcategories[$mainCat['id']] = $stmtSub->fetchAll();
}

// If a category is selected, fetch its details and products
if ($selectedCategoryId) {
    // Get category details
    $stmtCat = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
    $stmtCat->execute([$selectedCategoryId]);
    $selectedCategory = $stmtCat->fetch();

    if ($selectedCategory) {
        // Check if this is a main category (parent_id IS NULL)
        if ($selectedCategory['parent_id'] === null) {
            // This is a main category, get all its subcategories
            $stmtSub = $pdo->prepare("SELECT id FROM categories WHERE parent_id = ?");
            $stmtSub->execute([$selectedCategoryId]);
            $subcategoryIds = $stmtSub->fetchAll(PDO::FETCH_COLUMN);

            // Include the main category ID as well
            $allCategoryIds = array_merge([$selectedCategoryId], $subcategoryIds);

            // Create placeholders for the IN clause
            $placeholders = str_repeat('?,', count($allCategoryIds) - 1) . '?';

            // Fetch products from main category and all subcategories
            $stmtProd = $pdo->prepare("SELECT * FROM products WHERE category_id IN ($placeholders) AND is_active = 1 ORDER BY name ASC");
            $stmtProd->execute($allCategoryIds);
            $products = $stmtProd->fetchAll();
        } else {
            // This is a subcategory, fetch products only from this subcategory
            $stmtProd = $pdo->prepare("SELECT * FROM products WHERE category_id = ? AND is_active = 1 ORDER BY name ASC");
            $stmtProd->execute([$selectedCategoryId]);
            $products = $stmtProd->fetchAll();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Categories - Jungle Mart</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="../css/navbar.css">
  <link rel="stylesheet" href="../css/categories.css">
  <link rel="stylesheet" href="../css/footer.css">
  <script defer src="../js/script.js"></script>
  <!-- Cart wiring (ensures .add-to-cart buttons work) -->
  <script defer src="../js/cart.js"></script>
</head>
<body>

<?php include '../partials/navbar.php'; ?>

<div class="container fade-in">
  <h1>Categories</h1>

  <div class="categories-layout">
    <!-- Left Sidebar: Categories and Subcategories -->
    <div class="categories-sidebar">
      <h2>All Categories</h2>
      <div class="categories-list">
        <?php foreach ($mainCategories as $cat): ?>
          <div class="category-item">
            <a href="categories.php?id=<?php echo $cat['id']; ?>" class="category-link <?php echo ($selectedCategoryId == $cat['id']) ? 'active' : ''; ?>">
              <?php echo htmlspecialchars($cat['name']); ?>
            </a>
            <?php if (!empty($subcategories[$cat['id']])): ?>
              <div class="subcategories-list">
                <?php foreach ($subcategories[$cat['id']] as $subcat): ?>
                  <a href="categories.php?id=<?php echo $subcat['id']; ?>" class="subcategory-link <?php echo ($selectedCategoryId == $subcat['id']) ? 'active' : ''; ?>">
                    <?php echo htmlspecialchars($subcat['name']); ?>
                  </a>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Right Content: Products -->
    <div class="products-content">
      <?php if ($selectedCategory): ?>
        <div class="products-header">
          <h2>Products in <?php echo htmlspecialchars($selectedCategory['name']); ?></h2>
          <p><?php echo count($products); ?> products found</p>
        </div>

        <?php if (!empty($products)): ?>
          <div class="products-grid">
            <?php foreach ($products as $product): ?>
              <div class="product-card">
                <div class="product-image">
                  <?php
                  $images = json_decode($product['images'], true);
                  $imagePath = !empty($images) ? '../products/' . $images[0] : '../images and logo/placeholder.png';
                  ?>
                  <img src="<?php echo htmlspecialchars($imagePath); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" onerror="this.src='../images and logo/placeholder.png'">
                </div>
                <div class="product-info">
                  <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                  <p class="product-description"><?php echo htmlspecialchars(substr($product['description'], 0, 100)) . '...'; ?></p>
                  <div class="product-price">
                    <span class="price">₹<?php echo number_format($product['price'], 2); ?></span>
                    <?php if ($product['wholesale_price']): ?>
                      <span class="wholesale-price">Wholesale: ₹<?php echo number_format($product['wholesale_price'], 2); ?></span>
                    <?php endif; ?>
                  </div>
                  <div class="product-actions">
                    <button class="btn add-to-cart"
                      data-id="<?php echo $product['id']; ?>"
                      data-title="<?php echo htmlspecialchars($product['name']); ?>"
                      data-price="<?php echo $product['price']; ?>"
                      data-wholesale="<?php echo $product['wholesale_price']; ?>"
                      data-moq="<?php echo $product['min_order_quantity']; ?>">
                      Add to Cart
                    </button>
                    <a href="product.php?id=<?php echo $product['id']; ?>" class="btn secondary">View Details</a>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <div class="no-products">
            <p>No products found in this category.</p>
            <a href="categories.php" class="btn">Browse All Categories</a>
          </div>
        <?php endif; ?>
      <?php else: ?>
        <div class="welcome-message">
          <h2>Welcome to Jungle Mart Categories</h2>
          <p>Select a category from the sidebar to view products.</p>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php include '../partials/footer.php'; ?>

</body>
</html>
