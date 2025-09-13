<?php
// /junglemart/admin/manage_categories.php
$page_title = 'Manage Categories • Jungle Mart';
require_once __DIR__ . '/admin_header.php'; // includes DB + auth + CSS + opens <main>

/* -----------------------------
   Helpers (safe DB access)
----------------------------- */
function safeCount(PDO $pdo, string $table): int {
  try {
    $stmt = $pdo->query("SELECT COUNT(*) AS c FROM `$table`");
    return (int)$stmt->fetchColumn();
  } catch (Throwable $e) { return 0; }
}

function safeRows(PDO $pdo, string $sql, array $args = []): array {
  try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($args);
    return $stmt->fetchAll();
  } catch (Throwable $e) { return []; }
}

/* -----------------------------
   Query Params (search/sort/pagination)
----------------------------- */
$q        = trim($_GET['q'] ?? '');
$page     = max(1, (int)($_GET['page'] ?? 1));
$per_page = max(5, min(50, (int)($_GET['per_page'] ?? 10)));

$whereSql = 'WHERE 1=1';
$args = [];
if ($q !== '') {
  $whereSql .= ' AND (c.name LIKE ? OR c.description LIKE ?)';
  $like = "%{$q}%";
  $args[] = $like; $args[] = $like;
}

// Count
$countSql = "SELECT COUNT(*) AS c FROM categories c $whereSql";
$stmt = $pdo->prepare($countSql);
$stmt->execute($args);
$total = (int)$stmt->fetchColumn();

$pages = max(1, (int)ceil($total / $per_page));
$offset = ($page - 1) * $per_page;

// Fetch rows
$sql = "SELECT c.id, c.name, c.description, c.is_active, c.created_at,
               COUNT(p.id) AS product_count
        FROM categories c
        LEFT JOIN products p ON p.category_id = c.id
        $whereSql
        GROUP BY c.id
        ORDER BY c.created_at DESC
        LIMIT $per_page OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($args);
$rows = $stmt->fetchAll();

// Helper to build links preserving filters
function linkWith(array $extra = []): string {
  $params = array_merge($_GET, $extra);
  return 'manage_categories.php?' . http_build_query($params);
}
?>
  <section class="dashboard-section">
    <h2>Manage Categories</h2>

    <!-- Top toolbar -->
    <form class="toolbar" method="get" action="manage_categories.php">
      <div class="search">
        <input type="text" name="q" placeholder="Search by name or description…" value="<?= htmlspecialchars($q) ?>">
      </div>
      <select name="per_page" class="btn" onchange="this.form.submit()">
        <?php foreach ([10,20,30,50] as $n): ?>
          <option value="<?= $n ?>" <?= $per_page===$n?'selected':'' ?>><?= $n ?>/page</option>
        <?php endforeach; ?>
      </select>
      <button class="btn primary" type="submit">Search</button>
      <a class="btn secondary" href="add_category.php">➕ Add Category</a>
    </form>

    <!-- Table -->
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Name</th>
            <th>Description</th>
            <th>Products</th>
            <th>Status</th>
            <th>Created</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!$rows): ?>
            <tr><td colspan="6" class="muted" style="text-align:center;padding:2rem;">No categories found.</td></tr>
          <?php else: foreach ($rows as $r): ?>
            <tr>
              <td>
                <div style="font-weight:600"><?= htmlspecialchars($r['name']) ?></div>
              </td>
              <td class="muted"><?= htmlspecialchars($r['description'] ?: '—') ?></td>
              <td><?= (int)$r['product_count'] ?></td>
              <td>
                <?php if ((int)$r['is_active'] === 1): ?>
                  <span class="badge green">Active</span>
                <?php else: ?>
                  <span class="badge red">Inactive</span>
                <?php endif; ?>
              </td>
              <td class="muted"><?= htmlspecialchars(date('d M Y', strtotime($r['created_at'] ?? 'now'))) ?></td>
              <td class="row-actions">
                <a class="btn" href="edit_category.php?id=<?= (int)$r['id'] ?>">Edit</a>
                <a class="btn secondary" href="manage_products.php?q=<?= urlencode($r['name']) ?>">View Products</a>
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

  <?php require_once __DIR__ . '/admin_footer.php'; // closes section + prints footer ?>
