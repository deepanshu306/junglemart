<?php
// /junglemart/admin/add_category.php
$page_title = 'Add Category • Jungle Mart';
require_once __DIR__ . '/admin_header.php'; // includes DB + auth + CSS + opens <main>

$errors = [];
$success = false;

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['name'] ?? '');
  $description = trim($_POST['description'] ?? '');
  $is_active = isset($_POST['is_active']) ? 1 : 0;

  // Validation
  if ($name === '') {
    $errors[] = 'Category name is required.';
  }

  // Check if name already exists
  if ($name !== '') {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM categories WHERE name = ?");
    $stmt->execute([$name]);
    if ($stmt->fetchColumn() > 0) {
      $errors[] = 'Category name already exists.';
    }
  }

  if (empty($errors)) {
    try {
      $stmt = $pdo->prepare("INSERT INTO categories (name, description, is_active, created_at) VALUES (?, ?, ?, NOW())");
      $stmt->execute([$name, $description, $is_active]);
      $success = true;
      // Reset form
      $name = $description = '';
      $is_active = 1;
    } catch (Exception $e) {
      $errors[] = 'Failed to add category. Please try again.';
    }
  }
}
?>
  <section class="dashboard-section">
    <h2>Add New Category</h2>

    <?php if ($success): ?>
      <div class="success-message">Category added successfully!</div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
      <div class="error-message">
        <ul>
          <?php foreach ($errors as $error): ?>
            <li><?= htmlspecialchars($error) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <form method="post" action="admin/add_category.php" class="admin-form">
      <div class="form-group">
        <label for="name">Category Name <span class="required">*</span></label>
        <input type="text" id="name" name="name" value="<?= htmlspecialchars($name ?? '') ?>" required maxlength="100">
      </div>

      <div class="form-group">
        <label for="description">Description</label>
        <textarea id="description" name="description" rows="4" maxlength="500"><?= htmlspecialchars($description ?? '') ?></textarea>
      </div>

      <div class="form-group">
        <label class="checkbox-label">
          <input type="checkbox" name="is_active" value="1" <?= isset($is_active) && $is_active ? 'checked' : 'checked' ?>>
          Active
        </label>
      </div>

      <div class="form-actions">
        <button type="submit" class="btn primary">Add Category</button>
        <a href="admin/manage_categories.php" class="btn">Cancel</a>
      </div>
    </form>

  <?php require_once __DIR__ . '/admin_footer.php'; // closes section + prints footer ?>
