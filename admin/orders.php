<?php
// admin/orders.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$page_title = 'Orders • Jungle Mart';
require_once __DIR__ . '/admin_header.php'; // includes DB + auth + CSS + opens <main>

/* -----------------------------
   Helpers (safe DB access)
----------------------------- */
function safeCount(PDO $pdo, string $table): int {
  try {
    $stmt = $pdo->query("SELECT COUNT(*) AS c FROM `$table`");
    return (int)$stmt->fetchColumn();
  } catch (Throwable $e) { 
    error_log("safeCount error: " . $e->getMessage());
    return 0; 
  }
}

function safeRows(PDO $pdo, string $sql, array $args = []): array {
  try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($args);
    return $stmt->fetchAll();
  } catch (Throwable $e) { 
    error_log("safeRows error: " . $e->getMessage());
    return []; 
  }
}

function rupee(float $n): string {
  return '₹' . number_format($n, 2);
}

/* -----------------------------
   Query Params (search/sort/pagination)
----------------------------- */
$q        = trim($_GET['q'] ?? '');
$status   = $_GET['status'] ?? '';
$page     = max(1, (int)($_GET['page'] ?? 1));
$per_page = max(5, min(50, (int)($_GET['per_page'] ?? 10)));

$whereSql = 'WHERE 1=1';
$args = [];
if ($q !== '') {
  $whereSql .= ' AND (o.order_number LIKE ? OR o.customer_name LIKE ? OR o.customer_email LIKE ?)';
  $like = "%{$q}%";
  $args[] = $like; $args[] = $like; $args[] = $like;
}
if ($status !== '') {
  $whereSql .= ' AND o.status = ?';
  $args[] = $status;
}

$countSql = "SELECT COUNT(*) AS c FROM (
  SELECT o.id, u.full_name AS customer_name, u.email AS customer_email, u.phone AS customer_phone, o.total_amount, o.status, o.created_at, o.updated_at, 'order' AS type
  FROM orders o
  LEFT JOIN users u ON o.buyer_id = u.id
  UNION ALL
  SELECT q.id, q.customer_name, q.customer_email, q.customer_phone, q.total_amount, '' AS status, q.created_at, '' AS updated_at, 'quotation' AS type FROM quotations q
) AS combined
$whereSql";
$stmt = $pdo->prepare($countSql);
$stmt->execute($args);
$total = (int)$stmt->fetchColumn();

$pages = max(1, (int)ceil($total / $per_page));
$offset = ($page - 1) * $per_page;

$sql = "SELECT * FROM (
  SELECT o.id, o.order_number, u.full_name AS customer_name, u.email AS customer_email, u.phone AS customer_phone,
         o.total_amount, o.status, o.created_at, o.updated_at, 'order' AS type
  FROM orders o
  LEFT JOIN users u ON o.buyer_id = u.id
  UNION ALL
  SELECT q.id, NULL AS order_number, q.customer_name, q.customer_email, q.customer_phone,
         q.total_amount, '' AS status, q.created_at, '' AS updated_at, 'quotation' AS type
  FROM quotations q
) AS combined
$whereSql
ORDER BY combined.created_at DESC
LIMIT $per_page OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($args);
$rows = $stmt->fetchAll();

// Additional processing to distinguish orders and quotations in display
foreach ($rows as &$row) {
    if ($row['type'] === 'quotation') {
        $row['order_number'] = 'Q-' . str_pad($row['id'], 6, '0', STR_PAD_LEFT);
    }
}
unset($row);

// Helper to build links preserving filters
function linkWith(array $extra = []): string {
  $params = array_merge($_GET, $extra);
  return 'orders.php?' . http_build_query($params);
}
?>
  <section class="dashboard-section">
    <h2>Orders</h2>

    <!-- Top toolbar -->
    <form class="toolbar" method="get" action="orders.php">
      <div class="search">
        <input type="text" name="q" placeholder="Search by order number, customer name, or email…" value="<?= htmlspecialchars($q) ?>">
      </div>
      <select name="status" class="btn" onchange="this.form.submit()">
        <option value="">All Status</option>
        <option value="pending" <?= $status==='pending'?'selected':'' ?>>Pending</option>
        <option value="processing" <?= $status==='processing'?'selected':'' ?>>Processing</option>
        <option value="shipped" <?= $status==='shipped'?'selected':'' ?>>Shipped</option>
        <option value="delivered" <?= $status==='delivered'?'selected':'' ?>>Delivered</option>
        <option value="cancelled" <?= $status==='cancelled'?'selected':'' ?>>Cancelled</option>
      </select>
      <select name="per_page" class="btn" onchange="this.form.submit()">
        <?php foreach ([10,20,30,50] as $n): ?>
          <option value="<?= $n ?>" <?= $per_page===$n?'selected':'' ?>><?= $n ?>/page</option>
        <?php endforeach; ?>
      </select>
      <button class="btn primary" type="submit">Search</button>
    </form>

    <!-- Table -->
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Order #</th>
            <th>Customer</th>
            <th>Total</th>
            <th>Status</th>
            <th>Created</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!$rows): ?>
            <tr><td colspan="6" class="muted" style="text-align:center;padding:2rem;">No orders found.</td></tr>
          <?php else: foreach ($rows as $r): ?>
            <tr>
              <td>
                <div style="font-weight:600"><?= htmlspecialchars($r['order_number'] ?: ('#' . (int)$r['id'])) ?></div>
              </td>
              <td>
                <div style="font-weight:600"><?= htmlspecialchars($r['customer_name']) ?></div>
                <div class="muted" style="font-size:.85rem;"><?= htmlspecialchars($r['customer_email']) ?></div>
                <div class="muted" style="font-size:.85rem;"><?= htmlspecialchars($r['customer_phone']) ?></div>
              </td>
              <td class="price"><strong><?= isset($r['total_amount']) ? rupee((float)$r['total_amount']) : '—' ?></strong></td>
              <td>
                <?php
                  $st = strtolower((string)($r['status'] ?? ''));
                  $badgeCls = ($st === 'delivered') ? 'green' : (($st === 'cancelled') ? 'red' : 'yellow');
                ?>
                <span class="badge <?= $badgeCls ?>"><?= htmlspecialchars($r['status'] ?? '—') ?></span>
              </td>
              <td class="muted"><?= htmlspecialchars(isset($r['created_at']) ? date('d M Y', strtotime($r['created_at'])) : '—') ?></td>
              <td class="row-actions">
          <a class="btn" href="view_order.php?id=<?= (int)$r['id'] ?>&type=<?= $r['type'] ?>">View</a>
          <a class="btn secondary" href="edit_order.php?id=<?= (int)$r['id'] ?>&type=<?= $r['type'] ?>">Edit</a>
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
