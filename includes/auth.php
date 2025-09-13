<?php
// includes/auth.php
if (session_status() === PHP_SESSION_NONE) {
  $cookieParams = session_get_cookie_params();
  $isSecure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
  // Allow secure to be false on localhost HTTP for session cookie to work
  if (isset($_SERVER['HTTP_HOST']) && (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false || strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false)) {
    $isSecure = false;
  }
  session_set_cookie_params([
    'lifetime' => $cookieParams['lifetime'],
    'path' => $cookieParams['path'],
    'domain' => $cookieParams['domain'],
    'secure' => $isSecure,
    'httponly' => true,
    'samesite' => 'Lax' // or 'None' if using HTTPS and cross-site cookies needed
  ]);
  session_start();
}

/* ---- Session getters (compat) ---- */
function current_user_id() {
  return $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? null;
}
function current_role() {
  return $_SESSION['admin_role'] ?? $_SESSION['role'] ?? null; // supports 'admin' (legacy) and 'editor'/'superadmin'
}

/* ---- Core checks ---- */
function is_logged_in(): bool { return current_user_id() !== null; }

/** Admin if: legacy 'admin' OR new 'editor'/'superadmin' */
function is_admin(): bool {
  $r = strtolower((string) current_role());
  return in_array($r, ['admin','editor','superadmin'], true);
}

/* ---- Guards (paths adjusted to Jungle Mart) ---- */
function require_login() {
  if (!is_logged_in()) { header('Location: admin/admin_login.php'); exit; }
}
function require_admin() {
  if (!is_admin()) { header('Location: index.php'); exit; }
}

/* ---- Optional: specific role requirement ---- */
function require_role($roles): void {
  if (is_string($roles)) { $roles = [$roles]; }
  $r = strtolower((string) current_role());
  if (!in_array($r, array_map('strtolower',$roles), true)) {
    header('Location: admin/admin_dashboard.php?err=Not+allowed'); exit;
  }
}

/* ---- Lightweight flash helpers (optional) ---- */
if (!function_exists('flash')) {
  function flash(string $type, string $msg): void { $_SESSION['_flash'][] = ['t'=>$type,'m'=>$msg]; }
}
if (!function_exists('render_flashes')) {
  function render_flashes(): string {
    if (empty($_SESSION['_flash'])) return '';
    $html = '';
    foreach ($_SESSION['_flash'] as $f) {
      $cls = $f['t']==='success' ? 'success-message' : 'error-message';
      $html .= '<div class="'.$cls.'">'.htmlspecialchars($f['m']).'</div>';
    }
    unset($_SESSION['_flash']);
    return $html;
  }
}
