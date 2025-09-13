<?php
// /junglemart/admin/edit_order.php
$page_title = 'Edit Order • Jungle Mart';
require_once __DIR__ . '/admin_header.php'; // includes DB + auth + CSS + opens <main>

$id = (int)($_GET['id'] ?? 0);
$type = $_GET['type'] ?? 'order';

// If type parameter is missing, try to detect type by checking existence in orders or quotations
if (!isset($_GET['type'])) {
  $stmt = $pdo->prepare("SELECT 1 FROM orders WHERE id = ? LIMIT 1");
  $stmt->execute([$id]);
  if ($stmt->fetch()) {
    $type = 'order';
  } else {
    $stmt = $pdo->prepare("SELECT 1 FROM quotations WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    if ($stmt->fetch()) {
      $type = 'quotation';
    } else {
      $type = 'order'; // default fallback
    }
  }
}

if ($type === 'quotation') {
  // Fetch quotation
  $stmt = $pdo->prepare("SELECT * FROM quotations WHERE id = ? LIMIT 1");
  $stmt->execute([$id]);
  $order = $stmt->fetch();
} else {
  // Fetch order
  $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? LIMIT 1");
  $stmt->execute([$id]);
  $order = $stmt->fetch();
}

if (!$order) {
  echo '<div class="error-message">Order not found.</div>';
  require_once __DIR__ . '/admin_footer.php';
  exit;
}

$statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];

function rupee(float $n): string {
  return '₹' . number_format($n, 2);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $status = $_POST['status'] ?? '';
  $notes = trim($_POST['notes'] ?? '');

  if (!in_array($status, $statuses)) {
    $error = 'Invalid status selected.';
  } else {
    try {
      if ($type === 'quotation') {
        // Update quotations table - status and updated_at columns do not exist, so only update notes
        $stmt = $pdo->prepare("UPDATE quotations SET notes = ? WHERE id = ?");
        $stmt->execute([$notes, $id]);
        $success = 'Quotation updated successfully.';
        // Refresh quotation data
        $stmt = $pdo->prepare("SELECT * FROM quotations WHERE id = ?");
        $stmt->execute([$id]);
        $order = $stmt->fetch();
      } else {
        $stmt = $pdo->prepare("UPDATE orders SET status = ?, notes = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$status, $notes, $id]);
        $success = 'Order updated successfully.';
        // Refresh order data
        $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt->execute([$id]);
        $order = $stmt->fetch();
      }
    } catch (Exception $e) {
      $error = 'Failed to update order.';
    }
  }
}
?>
  <section class="dashboard-section">
    <h2>Edit Order</h2>

    <?php if (isset($error)): ?>
      <div class="error-message"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if (isset($success)): ?>
      <div class="success-message"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

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

      <div class="info-section">
        <h4>Update Order Status</h4>
        <form method="post" action="edit_order.php?id=<?= $id ?>" class="admin-form">
          <div class="form-group">
            <label for="status">Status</label>
            <select id="status" name="status" required>
              <?php foreach ($statuses as $s): ?>
                <option value="<?= $s ?>" <?= ($order['status'] ?? '') === $s ? 'selected' : '' ?>>
                  <?= ucfirst($s) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-group">
            <label for="notes">Admin Notes</label>
            <textarea id="notes" name="notes" rows="4" placeholder="Internal notes about this order..."><?= htmlspecialchars($order['notes'] ?? '') ?></textarea>
          </div>

          <div class="form-actions">
            <button type="submit" class="btn primary">Update Order</button>
            <a href="view_order.php?id=<?= $id ?>" class="btn">View Details</a>
            <a href="orders.php" class="btn">Back to Orders</a>
          </div>
        </form>
      </div>
    </div>

  <?php require_once __DIR__ . '/admin_footer.php'; // closes section + prints footer ?>
