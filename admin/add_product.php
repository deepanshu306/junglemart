<?php
// /junglemart/admin/add_product.php
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

// Serve sample CSV if requested
if (isset($_GET['sample'])) {
  header('Content-Type: text/csv');
  header('Content-Disposition: attachment; filename="junglemart_products_sample.csv"');
  echo "name,sku,category_id,category_name,price,stock_quantity,is_active,short_description,description,images\n";
  echo "\"Areca Palm 6in\",\"ARE-6\",\"24\",\"Plants\",499,50,1,\"Indoor palm\",\"Air-purifying indoor palm\",\"products/areca/1.jpg,products/areca/2.jpg\"\n";
  echo "\"Ceramic Pot White M\",\"POT-WHT-M\",\"25\",\"Pots\",299,100,1,\"Medium pot\",\"Glossy ceramic pot\",\"products/pots/white-m.jpg\"\n";
  exit;
}

// Load categories for dropdown and for name→id mapping
try {
  $cats = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC")->fetchAll();
} catch (Throwable $e) {
  $cats = [];
}
$catNameToId = [];
foreach ($cats as $c) { $catNameToId[mb_strtolower($c['name'])] = (int)$c['id']; }

$adminName = $_SESSION['admin_name'] ?? 'Admin';
$ok = false; $errors = [];

/* ---------- helpers ---------- */
function toNullable($v) { $v = trim((string)$v); return $v === '' ? null : $v; }

function normalizeImages($raw) {
  $raw = trim((string)$raw);
  if ($raw === '') return null;
  // JSON array?
  $decoded = json_decode($raw, true);
  if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
    return json_encode(array_values($decoded), JSON_UNESCAPED_SLASHES);
  }
  // comma-separated → array
  $parts = array_values(array_filter(array_map('trim', explode(',', $raw)), function($x) { return $x !== ''; }));
  return $parts ? json_encode($parts, JSON_UNESCAPED_SLASHES) : null;
}

function insertOne(PDO $pdo, array $row): array {
  // expects: name, sku, price, stock_quantity, category_id, is_active, short_description, description, images
  $sql = "INSERT INTO products
            (category_id, name, sku, price, stock_quantity, is_active, short_description, description, images)
          VALUES
            (:category_id, :name, :sku, :price, :stock, :active, :short_desc, :descr, :images)";
  $stmt = $pdo->prepare($sql);
  $stmt->execute([
    ':category_id' => $row['category_id'],
    ':name'        => $row['name'],
    ':sku'         => $row['sku'],
    ':price'       => $row['price'],
    ':stock'       => $row['stock_quantity'],
    ':active'      => $row['is_active'],
    ':short_desc'  => $row['short_description'],
    ':descr'       => $row['description'],
    ':images'      => $row['images'],
  ]);
  return ['ok'=>true, 'id'=>$pdo->lastInsertId()];
}

/* ---------- SINGLE ADD ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['mode'] ?? '') === 'single') {
  if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
    $errors[] = 'Bad session. Please try again.';
  } else {
    $name        = trim($_POST['name'] ?? '');
    $sku         = toNullable($_POST['sku'] ?? '');
    $price       = (float)($_POST['price'] ?? 0);
    $stock       = (int)($_POST['stock_quantity'] ?? 0);
    $category_id = (int)($_POST['category_id'] ?? 0);
    $is_active   = isset($_POST['is_active']) ? 1 : 0;
    $short_desc  = toNullable($_POST['short_description'] ?? '');
    $desc        = toNullable($_POST['description'] ?? '');
    $images      = normalizeImages($_POST['images'] ?? '');

    if ($name === '') $errors[] = 'Name is required';
    if ($price < 0)   $errors[] = 'Price cannot be negative';
    if ($stock < 0)   $errors[] = 'Stock cannot be negative';

    if (!$errors) {
      try {
        insertOne($pdo, [
          'category_id'       => $category_id ?: null,
          'name'              => $name,
          'sku'               => $sku,
          'price'             => $price,
          'stock_quantity'    => $stock,
          'is_active'         => $is_active,
          'short_description' => $short_desc,
          'description'       => $desc,
          'images'            => $images,
        ]);
        $ok = true;
      } catch (Throwable $e) {
        $errors[] = 'Insert failed: ' . $e->getMessage();
      }
    }
  }
}

/* ---------- BULK UPLOAD (CSV) ---------- */
$bulkReport = []; $bulkTotals = ['ok'=>0,'fail'=>0];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['mode'] ?? '') === 'bulk') {
  if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
    $errors[] = 'Bad session for bulk upload. Please try again.';
  } elseif (!isset($_FILES['csv']) || $_FILES['csv']['error'] !== UPLOAD_ERR_OK) {
    $errors[] = 'Please choose a CSV file.';
  } else {
    $fh = fopen($_FILES['csv']['tmp_name'], 'r');
    if (!$fh) $errors[] = 'Could not read uploaded file.';
    if (!$errors) {
      // read header
      $header = fgetcsv($fh);
      if (!$header) $errors[] = 'CSV seems empty.';
      else {
        $header = array_map(function($h) { return strtolower(trim($h)); }, $header);
        // accepted headers
        // name,sku,category_id,category_name,price,stock_quantity,is_active,short_description,description,images
        $rows = [];
        while (($cols = fgetcsv($fh)) !== false) {
          if (count($cols) === 1 && trim($cols[0]) === '') continue; // skip blank
          $row = array_combine($header, array_map('trim', $cols));

          $lineErrs = [];
          $name  = $row['name'] ?? '';
          $sku   = $row['sku'] ?? null;
          $price = isset($row['price']) ? (float)$row['price'] : 0;
          $stock = isset($row['stock_quantity']) ? (int)$row['stock_quantity'] : 0;
          $isAct = isset($row['is_active']) && (string)$row['is_active'] !== '' ? (int)$row['is_active'] : 1;

          // category mapping
          $catId = null;
          if (!empty($row['category_id'])) {
            $catId = (int)$row['category_id'];
          } elseif (!empty($row['category_name'])) {
            $key = mb_strtolower($row['category_name']);
            $catId = $catNameToId[$key] ?? null;
            if ($catId === null) $lineErrs[] = "Unknown category_name '{$row['category_name']}'";
          }

          if (trim($name) === '') $lineErrs[] = 'name missing';
          if ($price < 0)         $lineErrs[] = 'price negative';
          if ($stock < 0)         $lineErrs[] = 'stock negative';

          $images = normalizeImages($row['images'] ?? '');

          if ($lineErrs) {
            $bulkReport[] = ['status'=>'fail','name'=>$name,'reason'=>implode('; ', $lineErrs)];
            $bulkTotals['fail']++;
          } else {
            try {
              insertOne($pdo, [
                'category_id'       => $catId,
                'name'              => $name,
                'sku'               => toNullable($sku),
                'price'             => $price,
                'stock_quantity'    => $stock,
                'is_active'         => $isAct ? 1 : 0,
                'short_description' => toNullable($row['short_description'] ?? ''),
                'description'       => toNullable($row['description'] ?? ''),
                'images'            => $images,
              ]);
              $bulkReport[] = ['status'=>'ok','name'=>$name,'reason'=>''];
              $bulkTotals['ok']++;
            } catch (Throwable $e) {
              $bulkReport[] = ['status'=>'fail','name'=>$name,'reason'=>'DB: '.$e->getMessage()];
              $bulkTotals['fail']++;
            }
          }
        }
        fclose($fh);
      }
    }
  }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Add Products • Jungle Mart</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="../css/admin.css">
  <style>
    .two-col{display:grid;grid-template-columns:1fr 1fr;gap:1.25rem}
    .two-col .full{grid-column:1/-1}
    .small{font-size:.9rem;color:var(--gray)}
    .report{margin-top:1rem;background:#fff;border-radius:12px;box-shadow:var(--shadow-sm);overflow:auto}
    .report table{width:100%;border-collapse:collapse}
    .report th,.report td{padding:10px 12px;border-bottom:1px solid var(--gray-light);text-align:left}
    .report th{background:#f9fafb}
    .pill{display:inline-block;padding:.2rem .55rem;border-radius:999px;font-size:.75rem;font-weight:700}
    .pill.ok{background:#dcfce7;color:#166534}
    .pill.fail{background:#fee2e2;color:#991b1b}
    .upload{background:#fff;padding:1rem;border-radius:12px;box-shadow:var(--shadow-sm)}
    .muted{color:var(--gray)}
    input[type="file"]{padding:.6rem;border:2px solid var(--gray-light);border-radius:10px;background:#fff}
  </style>
</head>
<body class="admin-panel">

  <div class="admin-container">
    <!-- Sidebar -->
    <aside class="admin-sidebar">
      <div class="admin-user-info">
        <h3><?= htmlspecialchars($adminName) ?></h3>
        <p class="muted">Administrator</p>
        <a class="logout-btn" href="logout.php">Logout</a>
      </div>

      <nav class="admin-menu">
        <a class="menu-item" href="admin_dashboard.php">Dashboard</a>
        <a class="menu-item" href="manage_products.php">Manage Products</a>
        <a class="menu-item active" href="#">Add Products</a>
        <a class="menu-item" href="manage_categories.php">Manage Categories</a>
      </nav>
    </aside>

    <!-- Main -->
    <main class="admin-main">
      <section class="dashboard-section">
        <h2>Add Products</h2>

        <?php if ($ok): ?>
          <div class="success-message">Product added successfully.</div>
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

        <!-- Single product form -->
        <form class="admin-form" method="post" action="add_product.php" autocomplete="off">
          <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">
          <input type="hidden" name="mode" value="single">

          <div class="two-col">
            <div class="form-group">
              <label for="name">Name *</label>
              <input id="name" name="name" type="text" required>
            </div>

            <div class="form-group">
              <label for="sku">SKU</label>
              <input id="sku" name="sku" type="text">
            </div>

            <div class="form-group">
              <label for="price">Price (₹) *</label>
              <input id="price" name="price" type="number" step="0.01" min="0" required>
            </div>

            <div class="form-group">
              <label for="stock_quantity">Stock Quantity *</label>
              <input id="stock_quantity" name="stock_quantity" type="number" step="1" min="0" required>
            </div>

            <div class="form-group">
              <label for="category_id">Category</label>
              <select id="category_id" name="category_id">
                <option value="">— None —</option>
                <?php foreach ($cats as $c): ?>
                  <option value="<?= (int)$c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="form-group">
              <label for="is_active">Status</label><br>
              <label style="display:inline-flex;align-items:center;gap:.5rem;">
                <input id="is_active" type="checkbox" name="is_active" value="1" checked> Active
              </label>
            </div>

            <div class="form-group full">
              <label for="short_description">Short Description</label>
              <textarea id="short_description" name="short_description" rows="3"></textarea>
            </div>

            <div class="form-group full">
              <label for="description">Description</label>
              <textarea id="description" name="description" rows="6"></textarea>
            </div>

            <div class="form-group full">
              <label for="images">Images (JSON array or comma-separated)</label>
              <textarea id="images" name="images" rows="3" placeholder='["products/01/img1.jpg","products/01/img2.jpg"] or products/01/img1.jpg,products/01/img2.jpg'></textarea>
            </div>
          </div>

          <div class="form-actions">
            <a class="btn" href="manage_products.php">← Back to list</a>
            <button class="btn primary" type="submit">Add Product</button>
          </div>
        </form>

        <!-- Bulk upload -->
        <div class="upload" style="margin-top:2rem">
          <h3>Bulk Add (CSV)</h3>
          <p class="small">
            Upload a CSV with headers:
            <code>name, sku, category_id, category_name, price, stock_quantity, is_active, short_description, description, images</code>
            (Use either <b>category_id</b> or <b>category_name</b>.)
          </p>
          <div class="form-actions" style="margin-bottom:1rem">
            <a class="btn" href="add_product.php?sample=1">Download sample CSV</a>
          </div>

          <form method="post" action="add_product.php" enctype="multipart/form-data">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">
            <input type="hidden" name="mode" value="bulk">
            <div class="two-col">
              <div class="form-group full">
                <label for="csv">CSV File</label>
                <input id="csv" type="file" name="csv" accept=".csv" required>
              </div>
            </div>
            <div class="form-actions">
              <button class="btn primary" type="submit">Upload & Import</button>
            </div>
          </form>

          <?php if ($bulkReport): ?>
            <div class="report">
              <table>
                <thead>
                  <tr><th>Status</th><th>Name</th><th>Notes</th></tr>
                </thead>
                <tbody>
                  <?php foreach ($bulkReport as $r): ?>
                    <tr>
                      <td><span class="pill <?= $r['status']==='ok'?'ok':'fail' ?>"><?= strtoupper($r['status']) ?></span></td>
                      <td><?= htmlspecialchars($r['name'] ?: '—') ?></td>
                      <td class="muted"><?= htmlspecialchars($r['reason'] ?: '') ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
            <p class="small muted" style="margin-top:.5rem">
              Imported: <b><?= $bulkTotals['ok'] ?></b> ✓ &nbsp;•&nbsp; Failed: <b><?= $bulkTotals['fail'] ?></b>
            </p>
          <?php endif; ?>
        </div>

      </section>
    </main>
  </div>

  <footer class="admin-footer">
    © <?= date('Y') ?> Jungle Mart • Admin Panel
  </footer>

</body>
</html>
