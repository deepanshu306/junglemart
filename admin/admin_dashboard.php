<?php
// /admin/admin_dashboard.php
$page_title = 'Dashboard • Jungle Mart';
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

function safeSum(PDO $pdo, string $table, string $col, string $where = '', array $args = []): float {
  try {
    $sql = "SELECT SUM($col) AS s FROM `$table`" . ($where ? " WHERE $where" : '');
    $stmt = $pdo->prepare($sql);
    $stmt->execute($args);
    $v = $stmt->fetchColumn();
    return $v !== null ? (float)$v : 0.0;
  } catch (Throwable $e) { return 0.0; }
}

function safeRows(PDO $pdo, string $sql, array $args = []): array {
  try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($args);
    return $stmt->fetchAll();
  } catch (Throwable $e) { return []; }
}

function rupee(float $n): string {
  return '₹' . number_format($n, 2);
}

/* -----------------------------
   Stats (with fallbacks)
----------------------------- */
$stats = [
  'products'     => safeCount($pdo, 'products'),
  'categories'   => safeCount($pdo, 'categories'),
  'orders'       => safeCount($pdo, 'orders'),
  'customers'    => safeCount($pdo, 'users'), // adjust to your table name if needed
  'low_stock'    => 0,
  'revenue_7d'   => 0.0,
];

// Low stock (<= 5)
$stats['low_stock'] = (function(PDO $pdo): int {
  try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM products WHERE stock_quantity <= 5");
    return (int)$stmt->fetchColumn();
  } catch (Throwable $e) { return 0; }
})($pdo);

// Revenue last 7 days (if orders table has total_amount + created_at)
$sevenDaysAgo = date('Y-m-d 00:00:00', strtotime('-7 days'));
$stats['revenue_7d'] = (function(PDO $pdo, string $since): float {
  // Try a common schema first
  $sum = safeSum($pdo, 'orders', 'total_amount', 'created_at >= :since', [':since' => $since]);
  if ($sum > 0) return $sum;
  // Fallback: try "amount" column name
  $sum = safeSum($pdo, 'orders', 'amount', 'created_at >= :since', [':since' => $since]);
  return $sum;
})($pdo, $sevenDaysAgo);

/* -----------------------------
   Lists (safe queries)
----------------------------- */
// Latest products (limit 5)
$latestProducts = safeRows(
  $pdo,
  "SELECT id, name, sku, price, stock_quantity, is_active, created_at
   FROM products
   ORDER BY created_at DESC
   LIMIT 5"
);

// Low stock (limit 5)
$lowStock = safeRows(
  $pdo,
  "SELECT id, name, sku, stock_quantity
   FROM products
   WHERE stock_quantity <= 5
   ORDER BY stock_quantity ASC, id ASC
   LIMIT 5"
);

// Recent orders (limit 5) – try common columns, else returns empty
$recentOrders = safeRows(
  $pdo,
  "SELECT id, order_number, customer_name, total_amount, status, created_at
   FROM orders
   ORDER BY created_at DESC
   LIMIT 5"
);
?>
  <section class="dashboard-section">
    <h2>Welcome back, <?= htmlspecialchars($_SESSION['admin_name'] ?? 'Admin') ?> 👋</h2>

    <!-- Stats -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon">🪴</div>
        <div class="stat-info">
          <h3><?= (int)$stats['products'] ?></h3>
          <p>Total Products</p>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">🗂️</div>
        <div class="stat-info">
          <h3><?= (int)$stats['categories'] ?></h3>
          <p>Categories</p>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">🧾</div>
        <div class="stat-info">
          <h3><?= (int)$stats['orders'] ?></h3>
          <p>Orders</p>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">👥</div>
        <div class="stat-info">
          <h3><?= (int)$stats['customers'] ?></h3>
          <p>Customers</p>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">💸</div>
        <div class="stat-info">
          <h3><?= rupee($stats['revenue_7d']) ?></h3>
          <p>Revenue (Last 7 Days)</p>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">⚠️</div>
        <div class="stat-info">
          <h3><?= (int)$stats['low_stock'] ?></h3>
          <p>Low Stock (≤ 5)</p>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions">
      <h3>Quick Actions</h3>
      <div class="action-buttons">
        <a class="btn primary" href="add_product.php">➕ Add Product</a>
        <a class="btn secondary" href="manage_products.php">🧰 Manage Products</a>
        <a class="btn primary" href="manage_categories.php">📁 Manage Categories</a>
        <a class="btn secondary" href="orders.php">📦 View Orders</a>
      </div>
    </div>

    <!-- Two-column area: Recent Orders & Latest Products -->
    <div class="two-col" style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-top:16px">
      <!-- Recent Orders -->
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th colspan="5">Recent Orders</th>
            </tr>
            <tr>
              <th>#</th>
              <th>Customer</th>
              <th>Total</th>
              <th>Status</th>
              <th>Date</th>
            </tr>
          </thead>
          <tbody>
          <?php if (!$recentOrders): ?>
            <tr><td colspan="5" class="muted" style="text-align:center;padding:1rem">No orders yet.</td></tr>
          <?php else: foreach ($recentOrders as $o): ?>
            <tr>
              <td><?= htmlspecialchars($o['order_number'] ?? ('#' . (int)$o['id'])) ?></td>
              <td><?= htmlspecialchars($o['customer_name'] ?? '—') ?></td>
              <td><strong><?= isset($o['total_amount']) ? rupee((float)$o['total_amount']) : '—' ?></strong></td>
              <td>
                <?php
                  $st = strtolower((string)($o['status'] ?? ''));
                  $badgeCls = ($st === 'paid' || $st === 'completed' || $st === 'delivered') ? 'green' : 'red';
                ?>
                <span class="badge <?= $badgeCls ?>"><?= htmlspecialchars($o['status'] ?? '—') ?></span>
              </td>
              <td class="muted"><?= htmlspecialchars(isset($o['created_at']) ? date('d M Y', strtotime($o['created_at'])) : '—') ?></td>
            </tr>
          <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>

      <!-- Latest Products -->
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th colspan="5">Latest Products</th>
            </tr>
            <tr>
              <th>Name</th>
              <th>SKU</th>
              <th>Price</th>
              <th>Stock</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
          <?php if (!$latestProducts): ?>
            <tr><td colspan="5" class="muted" style="text-align:center;padding:1rem">No products found.</td></tr>
          <?php else: foreach ($latestProducts as $p): ?>
            <tr>
              <td>
                <strong><?= htmlspecialchars($p['name']) ?></strong>
                <div class="muted" style="font-size:.85rem;">
                  <?= htmlspecialchars(isset($p['created_at']) ? date('d M Y', strtotime($p['created_at'])) : '') ?>
                </div>
              </td>
              <td class="sku"><?= htmlspecialchars($p['sku'] ?: '—') ?></td>
              <td class="price"><?= rupee((float)$p['price']) ?></td>
              <td><?= (int)$p['stock_quantity'] ?></td>
              <td>
                <?php if ((int)$p['is_active'] === 1): ?>
                  <span class="badge green">Active</span>
                <?php else: ?>
                  <span class="badge red">Inactive</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Low stock alert list -->
    <div class="table-wrap" style="margin-top:16px">
      <table>
        <thead>
          <tr>
            <th colspan="4">Low Stock Alerts (≤ 5)</th>
          </tr>
          <tr>
            <th>Product</th>
            <th>SKU</th>
            <th>Stock</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php if (!$lowStock): ?>
          <tr><td colspan="4" class="muted" style="text-align:center;padding:1rem;">No low stock items 🎉</td></tr>
        <?php else: foreach ($lowStock as $ls): ?>
          <tr>
            <td><?= htmlspecialchars($ls['name']) ?></td>
            <td class="sku"><?= htmlspecialchars($ls['sku'] ?: '—') ?></td>
            <td><strong><?= (int)$ls['stock_quantity'] ?></strong></td>
            <td class="row-actions">
              <a class="btn" href="admin/edit_product.php?id=<?= (int)$ls['id'] ?>">Edit</a>
              <a class="btn secondary" href="admin/manage_products.php?q=<?= urlencode($ls['sku'] ?: $ls['name']) ?>">Find</a>
            </td>
          </tr>
        <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>

  <?php require_once __DIR__ . 'admin_footer.php'; // closes section + prints footer ?>