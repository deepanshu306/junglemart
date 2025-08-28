<?php
session_start();
require_once 'db.php';

$subcategoryId = 154;
$subcategoryName = "LED Pathway Lights";

// Get products for this subcategory
$products = $pdo->prepare("SELECT * FROM products WHERE category_id = :id AND is_active = 1");
$products->execute([':id' => $subcategoryId]);
$products = $products->fetchAll();

// Helper function for images
function first_image($jsonImages) {
    $fallback = 'images/placeholder.png';
    if (empty($jsonImages)) return $fallback;
    $arr = json_decode($jsonImages, true);
    if (is_array($arr) && count($arr) > 0) return htmlspecialchars($arr[0]);
    return $fallback;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?php echo htmlspecialchars($subcategoryName); ?> - Jungle Mart</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="css/navbar.css">
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/footer.css">
  <script defer src="js/script.js"></script>
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="container fade-in">
  <h1><?php echo htmlspecialchars($subcategoryName); ?></h1>
  
  <?php if (count($products) === 0): ?>
    <p>No products found in this subcategory.</p>
  <?php else: ?>
    <div class="prod-grid">
      <?php foreach ($products as $p): ?>
        <div class="prod-card">
          <img src="<?php echo first_image($p['images']); ?>" alt="<?php echo htmlspecialchars($p['name']); ?>">
          <h3><?php echo htmlspecialchars($p['name']); ?></h3>
          <p class="price">
            Price Range: ₹<?php echo number_format((float)$p['price'],2); ?>
            <?php if (!empty($p['wholesale_price'])): ?>
              - ₹<?php echo number_format((float)$p['wholesale_price'],2); ?>
            <?php endif; ?>
            <br><small>MOQ: <?php echo (int)$p['min_order_quantity']; ?></small>
          </p>
          <button class="btn add-to-cart"
            data-id="<?php echo $p['id']; ?>"
            data-title="<?php echo htmlspecialchars($p['name']); ?>"
            data-price="<?php echo $p['price']; ?>"
            data-wholesale="<?php echo $p['wholesale_price']; ?>"
            data-moq="<?php echo $p['min_order_quantity']; ?>">
            Add to Cart
          </button>
          <a href="product.php?id=<?php echo $p['id']; ?>" class="btn email">View</a>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<?php include 'footer.php'; ?>

</body>
</html>