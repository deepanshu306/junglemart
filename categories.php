<?php
session_start();
require_once 'db.php';

// Check if category id is passed
$catId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch all categories with hierarchy
$allCats = $pdo->query("SELECT id, name, parent_id FROM categories ORDER BY parent_id IS NULL DESC, name ASC")->fetchAll();

// Build hierarchical category tree
function buildCategoryTree($categories, $parentId = null) {
    $tree = [];
    foreach ($categories as $category) {
        if ($category['parent_id'] == $parentId) {
            $children = buildCategoryTree($categories, $category['id']);
            if ($children) {
                $category['children'] = $children;
            }
            $tree[] = $category;
        }
    }
    return $tree;
}

$categoryTree = buildCategoryTree($allCats);

// Function to render category tree
function renderCategoryTree($categories, $currentCatId = 0, $level = 0) {
    $html = '';
    foreach ($categories as $category) {
        $hasChildren = !empty($category['children']);
        $isActive = $currentCatId == $category['id'];
        $indent = str_repeat('&nbsp;&nbsp;&nbsp;', $level);
        
        $html .= '<li>';
        $html .= '<a href="' . strtolower(str_replace([' ', '&', "'"], ['_', 'and', ''], $category['name'])) . '.php"';
        $html .= ' class="' . ($isActive ? 'active' : '') . '"';
        $html .= '>';
        $html .= $indent . htmlspecialchars($category['name']);
        $html .= '</a>';
        
        if ($hasChildren) {
            $html .= '<ul class="sub-categories">';
            $html .= renderCategoryTree($category['children'], $currentCatId, $level + 1);
            $html .= '</ul>';
        }
        
        $html .= '</li>';
    }
    return $html;
}

// Fetch products if category selected
$products = [];
$catName = "All Categories";

if ($catId > 0) {
    $stmt = $pdo->prepare("SELECT name FROM categories WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $catId]);
    $catRow = $stmt->fetch();
    if ($catRow) $catName = $catRow['name'];

    $stmt = $pdo->prepare("SELECT * FROM products WHERE category_id = :id AND is_active = 1");
    $stmt->execute([':id' => $catId]);
    $products = $stmt->fetchAll();
} else {
    $products = $pdo->query("SELECT * FROM products WHERE is_active = 1 LIMIT 20")->fetchAll();
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
  <title><?php echo htmlspecialchars($catName); ?> - Jungle Mart</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="css/navbar.css">
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/footer.css">
  <script defer src="js/script.js"></script>
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="container fade-in">
  <h1><?php echo htmlspecialchars($catName); ?></h1>

  <div class="categories-page">
    <!-- Sidebar Categories -->
    <aside class="cat-sidebar">
      <h3>Categories</h3>
      <ul>
        <?php echo renderCategoryTree($categoryTree, $catId); ?>
      </ul>
    </aside>

    <!-- Product List -->
    <div class="prod-list">
      <?php if (count($products) === 0): ?>
        <p>No products found in this category.</p>
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
  </div>
</div>

<?php include 'footer.php'; ?>

<style>
/* Extra CSS for categories page */
.categories-page {
  display: flex;
  gap: 20px;
  margin-top: 20px;
}
.cat-sidebar {
  flex: 1 1 250px;
  background: #fff;
  padding: 15px;
  border-radius: 10px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
.cat-sidebar h3 { margin-bottom: 10px; }
.cat-sidebar ul { list-style: none; padding: 0; }
.cat-sidebar ul li { margin: 4px 0; }
.cat-sidebar a {
  text-decoration: none;
  color: #333;
  font-weight: 500;
  transition: color 0.3s;
  display: block;
  padding: 4px 8px;
  border-radius: 4px;
}
.cat-sidebar a.active, .cat-sidebar a:hover { 
  color: #ff6a00;
  background-color: #fff8f0;
}
.cat-sidebar .sub-categories {
  margin-left: 15px;
  border-left: 2px solid #e0e0e0;
  padding-left: 10px;
}
.cat-sidebar .sub-categories li {
  margin: 2px 0;
}
.cat-sidebar .sub-categories a {
  font-weight: 400;
  font-size: 0.95em;
}
.prod-list { flex: 3; }
.prod-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit,minmax(220px,1fr));
  gap: 20px;
}
</style>

</body>
</html>
