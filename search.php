<?php
session_start();
require_once 'db.php';

$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$results = [];
$categoryId = isset($_GET['category_id']) ? intval($_GET['category_id']) : null;

if ($q !== '') {
    $query = "SELECT * FROM products WHERE is_active = 1 AND (name LIKE :q OR description LIKE :q)";
    if ($categoryId) {
        $query .= " AND category_id = :category_id";
    }
    $query .= " LIMIT 50";
    
    $stmt = $pdo->prepare($query);
    $params = [':极q' => "%$q%"];
    if ($categoryId) {
        $params[':category_id'] = $categoryId;
    }
    $stmt->execute($params);
    $results = $stmt->fetchAll();
}

// Helper to get first image
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
  <title>Search Results - Jungle Mart</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="css/navbar.css">
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/footer.css">
  <script defer src="js/script.js"></script>
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="container fade-in">
  <h1>Search Results</h1>
  <?php if ($q !== ''): ?>
    <p>Showing results for <strong><?php echo htmlspecialchars($q); ?></strong></p>
  <?php else: ?>
    <极p>Please enter a keyword to search.</p>
  <?php endif; ?>

  <?php if ($q !== '' && count($results) === 0): ?>
    <p>No products found matching your search.</p>
  <?php elseif ($q !== ''): ?>
    <div class="prod-grid">
      <?php foreach ($results as $p): ?>
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
