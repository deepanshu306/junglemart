<?php
session_start();
require_once 'db.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    die("Invalid product ID.");
}

// Fetch product
$stmt = $pdo->prepare("
    SELECT p.*, u.company_name, u.full_name AS seller_name, u.email AS seller_email
    FROM products p
    LEFT JOIN users u ON p.seller_id = u.id
    WHERE p.id = :id AND p.is_active = 1
    LIMIT 1
");
$stmt->execute([':id' => $id]);
$product = $stmt->fetch();

if (!$product) {
    die("Product not found.");
}

// Helper to fetch image
function first_image($jsonImages) {
    $fallback = 'images/placeholder.png';
    if (empty($jsonImages)) return $fallback;
    $arr = json_decode($jsonImages, true);
    if (is_array($arr) && count($arr) > 0) {
        return htmlspecialchars($arr[0]);
    }
    return $fallback;
}

$images = json_decode($product['images'], true);
if (!is_array($images)) $images = [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title><?php echo htmlspecialchars($product['name']); ?> - Jungle Mart</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="css/navbar.css">
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/footer.css">
  <script defer src="js/script.js"></script>
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="container product-page">

  <!-- Left: Image Gallery -->
  <div class="product-gallery">
    <div class="main-image">
      <img id="mainImage" src="<?php echo first_image($product['images']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
    </div>
    <div class="thumbs">
      <?php foreach ($images as $img): ?>
        <img class="thumb" src="<?php echo htmlspecialchars($img); ?>" alt="thumb">
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Right: Details -->
  <div class="product-details">
    <h1><?php echo htmlspecialchars($product['name']); ?></h1>
    <p class="prod-seller">Sold by: <?php echo htmlspecialchars($product['company_name'] ?? 'Unknown Supplier'); ?></p>
    
    <div class="prod-pricing">
      <strong>Price:</strong> ₹<?php echo number_format((float)$product['price'], 2); ?><br>
      <?php if (!empty($product['wholesale_price'])): ?>
        <strong>Wholesale:</strong> ₹<?php echo number_format((float)$product['wholesale_price'], 2); ?><br>
      <?php endif; ?>
      <strong>MOQ:</strong> <?php echo (int)$product['min_order_quantity']; ?>
    </div>

    <p class="prod-desc"><?php echo nl2br(htmlspecialchars($product['description'] ?? 'No description available.')); ?></p>

    <div class="actions">
      <button class="btn add-to-cart"
        data-id="<?php echo $product['id']; ?>"
        data-title="<?php echo htmlspecialchars($product['name']); ?>"
        data-price="<?php echo $product['price']; ?>"
        data-wholesale="<?php echo $product['wholesale_price']; ?>"
        data-moq="<?php echo $product['min_order_quantity']; ?>">
        Add to Cart
      </button>
    </div>

    <div class="contact-seller">
      <h4>Contact Supplier</h4>
      <p>Email: <?php echo htmlspecialchars($product['seller_email'] ?? 'N/A'); ?></p>
    </div>
  </div>

</div>

<?php include 'footer.php'; ?>

<script>
// Image gallery thumb click
document.addEventListener('DOMContentLoaded', function() {
  const thumbs = document.querySelectorAll('.thumbs img');
  const mainImg = document.getElementById('mainImage');
  thumbs.forEach(t => {
    t.addEventListener('click', () => {
      mainImg.src = t.src;
    });
  });
});
</script>

</body>
</html>