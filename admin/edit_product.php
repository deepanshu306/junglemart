<?php
// admin/edit_product.php
require_once __DIR__ . '/../includes/db.php';

// Auth guard
if (empty($_SESSION['admin_id'])) {
  header('Location: admin_login.php?err=Please+log+in'); exit;
}

// CSRF
if (empty($_SESSION['csrf'])) {
  $_SESSION['csrf'] = function_exists('random_bytes')
    ? bin2hex(random_bytes(32))
    : bin2hex(openssl_random_pseudo_bytes(32));
}

// Validate ID
$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
  header('Location: manage_products.php?err=Invalid+product+id'); exit;
}

// Fetch categories for dropdown
try {
  $cats = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC")->fetchAll();
} catch (Throwable $e) {
  $cats = [];
}

// Load product
$stmt = $pdo->prepare("SELECT id, category_id, name, slug, description, short_description, price, min_order_quantity, stock_quantity, sku, images, is_active
                       FROM products WHERE id = ? LIMIT 1");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
  header('Location: manage_products.php?err=Product+not+found'); exit;
}

$errors = [];
$ok = false;

// Handle POST (update)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // CSRF check
  if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
    $errors[] = 'Bad session. Please try again.';
  } else {
    // Gather fields safely
    $name        = trim($_POST['name'] ?? '');
    $sku         = trim($_POST['sku'] ?? '');
    $price       = (float)($_POST['price'] ?? 0);
    $stock       = (int)($_POST['stock_quantity'] ?? 0);
    $category_id = (int)($_POST['category_id'] ?? 0);
    $is_active   = isset($_POST['is_active']) ? 1 : 0;
    $short_desc  = trim($_POST['short_description'] ?? '');
    $desc        = trim($_POST['description'] ?? '');
    $images_raw  = trim($_POST['images'] ?? '');

    // Basic validation
    if ($name === '') $errors[] = 'Name is required';
    if ($price < 0)   $errors[] = 'Price cannot be negative';
    if ($stock < 0)   $errors[] = 'Stock cannot be negative';

    // Validate images JSON if provided (store null if blank)
    $images = null;
    if ($images_raw !== '') {
      $decoded = json_decode($images_raw, true);
      if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
        $errors[] = 'Images must be a valid JSON array (e.g., ["path/img1.jpg","path/img2.jpg"])';
      } else {
        $images = json_encode(array_values($decoded), JSON_UNESCAPED_SLASHES);
      }
    }

    if (!$errors) {
      // Update
      $sql = "UPDATE products
              SET category_id = :category_id,
                  name = :name,
                  sku = :sku,
                  price = :price,
                  stock_quantity = :stock,
                  is_active = :is_active,
                  short_description = :short_desc,
                  description = :descr,
                  images = :images
              WHERE id = :id
              LIMIT 1";
      $stmt = $pdo->prepare($sql);
      $stmt->execute([
        ':category_id' => $category_id ?: null,
        ':name'        => $name,
        ':sku'         => $sku !== '' ? $sku : null,
        ':price'       => $price,
        ':stock'       => $stock,
        ':is_active'   => $is_active,
        ':short_desc'  => $short_desc !== '' ? $short_desc : null,
        ':descr'       => $desc !== '' ? $desc : null,
        ':images'      => $images, // can be null
        ':id'          => $id
      ]);

      // Refresh product after update
      $stmt = $pdo->prepare("SELECT id, category_id, name, slug, description, short_description, price, min_order_quantity, stock_quantity, sku, images, is_active
                             FROM products WHERE id = ? LIMIT 1");
      $stmt->execute([$id]);
      $product = $stmt->fetch();

      $ok = true;
    }
  }
}

$adminName = $_SESSION['admin_name'] ?? 'Admin';

// Helpers for form values
function sel($a,$b){ return (string)$a===(string)$b ? 'selected' : ''; }
function chk($v){ return (int)$v===1 ? 'checked' : ''; }
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Edit Product • Jungle Mart</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="../css/admin.css">
  <style>
    .form-grid{display:grid;grid-template-columns:1fr 1fr;gap:1rem}
    .form-grid .full{grid-column:1/-1}
    .meta{color:var(--gray);font-size:.9rem;margin-top:.5rem}
  </style>
</head>
<body class="admin-panel">

  <div class="admin-container">
    <!-- Sidebar -->
    <aside class="admin-sidebar">
      <div class="admin-user-info">
        <h3><?= htmlspecialchars($adminName) ?></h3>
        <p class="muted">Administrator</p>
        <a class="logout-btn" href="admin/logout.php">Logout</a>
      </div>

      <nav class="admin-menu">
        <a class="menu-item" href="admin/admin_dashboard.php">Dashboard</a>
        <a class="menu-item" href="admin/manage_products.php">Manage Products</a>
        <a class="menu-item active" href="#">Edit Product</a>
        <a class="menu-item" href="manage_categories.php">Manage Categories</a>
      </nav>
    </aside>

    <!-- Main -->
    <main class="admin-main">
      <section class="dashboard-section">
        <h2>Edit Product</h2>

        <?php if ($ok): ?>
          <div class="success-message">Product updated successfully.</div>
        <?php endif; ?>

        <?php if ($errors): ?>
          <div class="error-message">
            <b>Please fix the following:</b>
            <ul style="margin:8px 0 0 18px">
              <?php foreach ($errors as $e): ?>
                <li><?= htmlspecialchars($e) ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <form class="admin-form" method="post" action="edit_product.php?id=<?= (int)$product['id'] ?>" autocomplete="off">
          <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">

          <div class="form-grid">
            <div class="form-group">
              <label for="name">Name *</label>
              <input id="name" name="name" type="text" value="<?= htmlspecialchars($product['name'] ?? '') ?>" required>
            </div>

            <div class="form-group">
              <label for="sku">SKU</label>
              <input id="sku" name="sku" type="text" value="<?= htmlspecialchars($product['sku'] ?? '') ?>">
            </div>

            <div class="form-group">
              <label for="price">Price (₹) *</label>
              <input id="price" name="price" type="number" step="0.01" min="0" value="<?= htmlspecialchars((string)$product['price']) ?>" required>
            </div>

            <div class="form-group">
              <label for="stock_quantity">Stock Quantity *</label>
              <input id="stock_quantity" name="stock_quantity" type="number" step="1" min="0" value="<?= (int)$product['stock_quantity'] ?>" required>
            </div>

            <div class="form-group">
              <label for="category_id">Category</label>
              <select id="category_id" name="category_id">
                <option value="">— None —</option>
                <?php foreach ($cats as $c): ?>
                  <option value="<?= (int)$c['id'] ?>" <?= sel($product['category_id'], $c['id']) ?>>
                    <?= htmlspecialchars($c['name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="form-group">
              <label for="is_active">Status</label><br>
              <label style="display:inline-flex;align-items:center;gap:.5rem;">
                <input id="is_active" type="checkbox" name="is_active" value="1" <?= chk($product['is_active']) ?>> Active
              </label>
            </div>

            <div class="form-group full">
              <label for="short_description">Short Description</label>
              <textarea id="short_description" name="short_description" rows="3"><?= htmlspecialchars($product['short_description'] ?? '') ?></textarea>
            </div>

            <div class="form-group full">
              <label for="description">Description</label>
              <textarea id="description" name="description" rows="6"><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
            </div>

            <div class="form-group full">
              <label for="images">Images (JSON array)</label>
              <textarea id="images" name="images" rows="4" placeholder='["products/01/img1.jpg","products/01/img2.jpg"]'><?= htmlspecialchars($product['images'] ?? '') ?></textarea>
              <div class="meta">Leave blank to keep <i>NULL</i>. Must be a valid JSON array if provided.</div>
            </div>
          </div>

          <div class="form-actions">
            <a class="btn" href="manage_products.php">← Back to list</a>
            <button class="btn primary" type="submit">Save Changes</button>
          </div>
        </form>

      </section>
    </main>
  </div>

  <footer class="admin-footer">
    © <?= date('Y') ?> Jungle Mart • Admin Panel
  </footer>

</body>
</html>
