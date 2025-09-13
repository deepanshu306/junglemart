<?php
// /junglemart/admin/manage_products.php
require_once __DIR__ . '/../includes/db.php';

// Auth gate
if (empty($_SESSION['admin_id'])) {
  header('Location: admin_login.php?err=Please+log+in'); exit;
}

// CSRF
if (empty($_SESSION['csrf'])) {
  $_SESSION['csrf'] = function_exists('random_bytes')
    ? bin2hex(random_bytes(32))
    : bin2hex(openssl_random_pseudo_bytes(32));
}

/////////////////////////////
// Query Params (search/sort/pagination)
$q        = trim($_GET['q'] ?? '');
$sort     = $_GET['sort'] ?? 'created_at';
$dir      = strtolower($_GET['dir'] ?? 'desc') === 'asc' ? 'asc' : 'desc';
$page     = max(1, (int)($_GET['page'] ?? 1));
$per_page = max(5, min(50, (int)($_GET['per_page'] ?? 10)));

$allowedSort = ['name','price','stock_quantity','created_at','is_active'];
if (!in_array($sort, $allowedSort, true)) $sort = 'created_at';

// Build WHERE
$whereSql = 'WHERE 1=1';
$args = [];
if ($q !== '') {
  $whereSql .= ' AND (p.name LIKE ? OR p.sku LIKE ?)';
  $like = "%{$q}%";
  $args[] = $like; $args[] = $like;
}

// Count
$countSql = "SELECT COUNT(*) AS c
             FROM products p
             $whereSql";
$stmt = $pdo->prepare($countSql);
$stmt->execute($args);
$total = (int)$stmt->fetchColumn();

$pages = max(1, (int)ceil($total / $per_page));
$offset = ($page - 1) * $per_page;

// Fetch rows
$sql = "SELECT p.id, p.name, p.sku, p.price, p.stock_quantity, p.is_active, p.created_at,
               c.name AS category
        FROM products p
        LEFT JOIN categories c ON c.id = p.category_id
        $whereSql
        ORDER BY $sort $dir
        LIMIT $per_page OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($args);
$rows = $stmt->fetchAll();

// Helper to build links preserving filters
function linkWith(array $extra = []): string {
  $params = array_merge($_GET, $extra);
  return 'manage_products.php?' . http_build_query($params);
}
// Toggle sort direction for a column
function sortLink(string $col): string {
  $current = $_GET['sort'] ?? 'created_at';
  $dir = strtolower($_GET['dir'] ?? 'desc') === 'asc' ? 'asc' : 'desc';
  $newDir = ($current === $col && $dir === 'asc') ? 'desc' : 'asc';
  return linkWith(['sort'=>$col,'dir'=>$newDir,'page'=>1]);
}

$page_title = 'Manage Products • Jungle Mart';
require_once __DIR__ . '/admin_header.php'; // includes DB + auth + CSS + opens <main>
?>
  <section class="dashboard-section">
    <h2>Manage Products</h2>

        <!-- Top toolbar -->
        <form class="toolbar" method="get" action="manage_products.php">
          <input type="hidden" name="sort" value="<?= htmlspecialchars($sort) ?>">
          <input type="hidden" name="dir" value="<?= htmlspecialchars($dir) ?>">
          <div class="search">
            <input type="text" name="q" placeholder="Search by name or SKU…" value="<?= htmlspecialchars($q) ?>">
          </div>
          <select name="per_page" class="btn" onchange="this.form.submit()">
            <?php foreach ([10,20,30,50] as $n): ?>
              <option value="<?= $n ?>" <?= $per_page===$n?'selected':'' ?>><?= $n ?>/page</option>
            <?php endforeach; ?>
          </select>
          <button class="btn primary" type="submit">Search</button>
          <a class="btn secondary" href="add_product.php">➕ Add Product</a>
        </form>

        <!-- Table -->
        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th><a href="<?= sortLink('name') ?>">Product</a></th>
                <th><a href="<?= sortLink('price') ?>">Price</a></th>
                <th><a href="<?= sortLink('stock_quantity') ?>">Stock</a></th>
                <th>Category</th>
                <th><a href="<?= sortLink('is_active') ?>">Status</a></th>
                <th><a href="<?= sortLink('created_at') ?>">Created</a></th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!$rows): ?>
                <tr><td colspan="7" class="muted" style="text-align:center;padding:2rem;">No products found.</td></tr>
              <?php else: foreach ($rows as $r): ?>
                <tr>
                  <td>
                    <div style="font-weight:600"><?= htmlspecialchars($r['name']) ?></div>
                    <div class="sku">SKU: <?= htmlspecialchars($r['sku'] ?: '—') ?></div>
                  </td>
                  <td class="price">₹<?= number_format((float)$r['price'], 2) ?></td>
                  <td><?= (int)$r['stock_quantity'] ?></td>
                  <td><?= htmlspecialchars($r['category'] ?: '—') ?></td>
                  <td>
                    <?php if ((int)$r['is_active'] === 1): ?>
                      <span class="badge green">Active</span>
                    <?php else: ?>
                      <span class="badge red">Inactive</span>
                    <?php endif; ?>
                  </td>
                  <td class="muted"><?= htmlspecialchars(date('d M Y', strtotime($r['created_at'] ?? 'now'))) ?></td>
                  <td class="row-actions">
                    <a class="btn" href="edit_product.php?id=<?= (int)$r['id'] ?>">Edit</a>

                    <!-- Toggle active -->
                    <form method="post" action="product_toggle.php" style="display:inline">
                      <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">
                      <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                      <input type="hidden" name="to" value="<?= (int)!$r['is_active'] ?>">
                      <button class="btn" type="submit"><?= $r['is_active'] ? 'Deactivate' : 'Activate' ?></button>
                    </form>

                    <!-- Delete -->
                    <form method="post" action="delete_product.php" style="display:inline" onsubmit="return confirm('Delete this product? This cannot be undone.');">
                      <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">
                      <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                      <button class="btn" type="submit" style="background:#fee;border:1px solid var(--error-border);color:#b91c1c">Delete</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="pagination">
          <?php
          $prev = max(1, $page-1);
          $next = min($pages, $page+1);
          ?>
          <a class="page-link" href="<?= linkWith(['page'=>1]) ?>">« First</a>
          <a class="page-link" href="<?= linkWith(['page'=>$prev]) ?>">‹ Prev</a>
          <span class="page-link active"><?= $page ?> / <?= $pages ?></span>
          <a class="page-link" href="<?= linkWith(['page'=>$next]) ?>">Next ›</a>
          <a class="page-link" href="<?= linkWith(['page'=>$pages]) ?>">Last »</a>
          <span class="muted">Total: <?= $total ?></span>
        </div>
      </section>

<?php require_once __DIR__ . '/admin_footer.php'; // closes section + prints footer ?>
