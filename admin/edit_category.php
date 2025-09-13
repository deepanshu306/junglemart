<?php
// /junglemart/admin/edit_category.php
$page_title = 'Edit Category • Jungle Mart';
require_once __DIR__ . '/admin_header.php'; // includes DB + auth + CSS + opens <main>

$id = (int)($_GET['id'] ?? 0);
$errors = [];
$success = false;

// Fetch category
$stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
$stmt->execute([$id]);
$category = $stmt->fetch();

if (!$category) {
  echo '<div class="error-message">Category not found.</div>';
  require_once __DIR__ . '/admin_footer.php';
  exit;
}

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['name'] ?? '');
  $description = trim($_POST['description'] ?? '');
  $is_active = isset($_POST['is_active']) ? 1 : 0;

  // Validation
  if ($name === '') {
    $errors[] = 'Category name is required.';
  }

  // Check if name already exists (excluding current)
  if ($name !== '') {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM categories WHERE name = ? AND id != ?");
    $stmt->execute([$name, $id]);
    if ($stmt->fetchColumn() > 0) {
      $errors[] = 'Category name already exists.';
    }
  }

  if (empty($errors)) {
    try {
      $stmt = $pdo->prepare("UPDATE categories SET name = ?, description = ?, is_active = ?, updated_at = NOW() WHERE id = ?");
      $stmt->execute([$name, $description, $is_active, $id]);
      $success = true;
      // Refresh data
      $category['name'] = $name;
      $category['description'] = $description;
      $category['is_active'] = $is_active;
    } catch (Exception $e) {
      $errors[] = 'Failed to update category. Please try again.';
    }
  }
}
?>
  <section class="dashboard-section">
    <h2>Edit Category</h2>

    <?php if ($success): ?>
      <div class="success-message">Category updated successfully!</div>
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

    <form method="post" action="edit_category.php?id=<?= $id ?>" class="admin-form">
      <div class="form-group">
        <label for="name">Category Name <span class="required">*</span></label>
        <input type="text" id="name" name="name" value="<?= htmlspecialchars($category['name']) ?>" required maxlength="100">
      </div>

      <div class="form-group">
        <label for="description">Description</label>
        <textarea id="description" name="description" rows="4" maxlength="500"><?= htmlspecialchars($category['description'] ?? '') ?></textarea>
      </div>

      <div class="form-group">
        <label class="checkbox-label">
          <input type="checkbox" name="is_active" value="1" <?= (int)$category['is_active'] ? 'checked' : '' ?>>
          Active
        </label>
      </div>

      <div class="form-actions">
        <button type="submit" class="btn primary">Update Category</button>
        <a href="manage_categories.php" class="btn">Cancel</a>
      </div>
    </form>

  <?php require_once __DIR__ . '/admin_footer.php'; // closes section + prints footer ?>
