<?php
// /junglemart/admin/view_order.php
$page_title = 'View Order • Jungle Mart';
require_once __DIR__ . '/admin_header.php'; // includes DB + auth + CSS + opens <main>

$id = (int)($_GET['id'] ?? 0);
$type = $_GET['type'] ?? 'order';

if ($type === 'quotation') {
  // Fetch quotation
  $stmt = $pdo->prepare("SELECT * FROM quotations WHERE id = ?");
  $stmt->execute([$id]);
  $order = $stmt->fetch();
} else {
  // Fetch order
  $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
  $stmt->execute([$id]);
  $order = $stmt->fetch();
}

if (!$order) {
  echo '<div class="error-message">Order not found.</div>';
  require_once __DIR__ . '/admin_footer.php';
  exit;
}

$orderItems = [];
try {
  if ($type === 'quotation') {
    $stmt = $pdo->prepare("SELECT qi.*, p.name AS product_name FROM quotation_items qi LEFT JOIN products p ON qi.product_id = p.id WHERE qi.quotation_id = ?");
    $stmt->execute([$id]);
    $orderItems = $stmt->fetchAll();
  } else {
    $stmt = $pdo->prepare("SELECT oi.*, p.name AS product_name FROM order_items oi LEFT JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
    $stmt->execute([$id]);
    $orderItems = $stmt->fetchAll();
  }
} catch (Exception $e) {
  // Order or quotation items table might not exist
}

function rupee(float $n): string {
  return '₹' . number_format($n, 2);
}
?>
  <section class="dashboard-section">
    <h2>Order Details</h2>

    <div class="order-details">
      <div class="order-header">
        <h3>Order #<?= htmlspecialchars($order['order_number'] ?: ('#' . (int)$order['id'])) ?></h3>
        <div class="order-status">
          <span class="badge <?= strtolower($order['status'] ?? '') === 'delivered' ? 'green' : 'yellow' ?>">
            <?= htmlspecialchars($order['status'] ?? 'Pending') ?>
          </span>
        </div>
      </div>

      <div class="order-info-grid">
        <div class="info-section">
          <h4>Customer Information</h4>
          <p><strong>Name:</strong> <?= htmlspecialchars($order['customer_name']) ?></p>
          <p><strong>Email:</strong> <?= htmlspecialchars($order['customer_email']) ?></p>
          <p><strong>Phone:</strong> <?= htmlspecialchars($order['customer_phone']) ?></p>
          <?php if (!empty($order['company'])): ?>
            <p><strong>Company:</strong> <?= htmlspecialchars($order['company']) ?></p>
          <?php endif; ?>
        </div>

        <div class="info-section">
          <h4>Order Information</h4>
          <p><strong>Order Date:</strong> <?= htmlspecialchars(date('d M Y H:i', strtotime($order['created_at']))) ?></p>
          <p><strong>Last Updated:</strong> <?= htmlspecialchars(date('d M Y H:i', strtotime($order['updated_at'] ?? $order['created_at']))) ?></p>
          <p><strong>Total Amount:</strong> <strong class="price"><?= isset($order['total_amount']) ? rupee((float)$order['total_amount']) : '—' ?></strong></p>
        </div>
      </div>

      <?php if (!empty($order['requirements'])): ?>
        <div class="info-section">
          <h4>Requirements</h4>
          <p><?= nl2br(htmlspecialchars($order['requirements'])) ?></p>
        </div>
      <?php endif; ?>

      <?php if (!empty($orderItems)): ?>
        <div class="info-section">
          <h4>Order Items</h4>
          <div class="table-wrap">
            <table>
              <thead>
                <tr>
                  <th>Product</th>
                  <th>Quantity</th>
                  <th>Price</th>
                  <th>Total</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($orderItems as $item): ?>
                  <tr>
                    <td><?= htmlspecialchars($item['product_name'] ?? '—') ?></td>
                    <td><?= (int)($item['quantity'] ?? 0) ?></td>
                    <td><?= isset($item['price']) ? rupee((float)$item['price']) : '—' ?></td>
                    <td><strong><?= isset($item['total']) ? rupee((float)$item['total']) : '—' ?></strong></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      <?php endif; ?>
    </div>

    <div class="form-actions">
      <a href="edit_order.php?id=<?= $id ?>" class="btn primary">Edit Order</a>
      <a href="orders.php" class="btn">Back to Orders</a>
    </div>

  <?php require_once __DIR__ . '/admin_footer.php'; // closes section + prints footer ?>
