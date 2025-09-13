<?php
// includes/flash.php
if (!isset($_SESSION)) { session_start(); }

function flash(string $type, string $msg): void {
  $_SESSION['_flash'][] = ['t'=>$type, 'm'=>$msg];
}
function render_flashes(): string {
  if (empty($_SESSION['_flash'])) return '';
  $html = '';
  foreach ($_SESSION['_flash'] as $f) {
    $cls = $f['t'] === 'success' ? 'success-message' : 'error-message';
    $html .= '<div class="'.$cls.'">'.htmlspecialchars($f['m']).'</div>';
  }
  unset($_SESSION['_flash']);
  return $html;
}
