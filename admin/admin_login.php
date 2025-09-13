<?php
require_once __DIR__ . '/../includes/db.php';

// Prevent cache to avoid ERR_CACHE_MISS on back/refresh
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

// Already logged in? go to dashboard
if (!empty($_SESSION['admin_id'])) {
  header('Location: admin_dashboard.php');
  exit;
}

// CSRF token (per session)
if (empty($_SESSION['csrf'])) {
  $_SESSION['csrf'] = function_exists('random_bytes')
    ? bin2hex(random_bytes(32))
    : bin2hex(openssl_random_pseudo_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // CSRF check
  if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
    $_SESSION['flash_err'] = 'Session expired. Please try again.';
    header('Location: admin_login.php'); exit;
  }

  $email = trim($_POST['email'] ?? '');
  $pass  = $_POST['password'] ?? '';

  if ($email === '' || $pass === '') {
    $_SESSION['flash_err'] = 'Enter email and password.';
    header('Location: admin_login.php'); exit;
  }

  error_log("Admin login attempt for email: $email");

  // Lookup admin in admin_users table
  $stmt = $pdo->prepare("SELECT id, name, email, password_hash, is_active
                         FROM admin_users WHERE email = ? LIMIT 1");
  $stmt->execute([$email]);
  $u = $stmt->fetch();

  if (!$u) {
    error_log("Admin login failed: user not found");
    $_SESSION['flash_err'] = 'Invalid credentials.';
    header('Location: admin_login.php'); exit;
  }

  if (!$u['is_active']) {
    error_log("Admin login failed: user inactive");
    $_SESSION['flash_err'] = 'Invalid credentials.';
    header('Location: admin_login.php'); exit;
  }

  if (!password_verify($pass, $u['password_hash'])) {
    error_log("Admin login failed: password mismatch");
    $_SESSION['flash_err'] = 'Invalid credentials.';
    header('Location: admin_login.php'); exit;
  }

  error_log("Admin login success for user ID: " . $u['id']);

  // Success: set session + redirect (PRG)
  session_regenerate_id(true);
  $_SESSION['admin_id']   = (int)$u['id'];
  $_SESSION['admin_name'] = $u['name'];
  $_SESSION['admin_role'] = 'admin';  // Set default admin role

  header('Location: admin_dashboard.php');
  exit;
}

// GET view (read & clear flash)
$err = $_SESSION['flash_err'] ?? ($_GET['err'] ?? '');
unset($_SESSION['flash_err']);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Admin Login • Jungle Mart</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="../css/admin.css?v=5">
</head>
<body class="admin-login">
  <div class="login-container">
    <div class="login-card">
      <div class="login-header">
        <h1>Admin Login</h1>
        <p>Sign in to manage Jungle Mart</p>
      </div>

      <?php if ($err): ?>
        <div class="error-message"><?= htmlspecialchars($err) ?></div>
      <?php endif; ?>

      <form class="login-form" method="post" action="admin_login.php" autocomplete="off" novalidate>
        <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">
        <div class="form-group">
          <label for="email">Email</label>
          <input id="email" name="email" type="email" required placeholder="admin@junglemart.in">
        </div>
        <div class="form-group">
          <label for="password">Password</label>
          <input id="password" name="password" type="password" required placeholder="••••••••">
        </div>
        <div class="form-actions">
          <button class="btn primary" type="submit">Sign in</button>
        </div>
      </form>
    </div>
  </div>
</body>
</html>