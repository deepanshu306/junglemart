<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

function esc(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

function first_image(?string $jsonImages): string {
  $fallback = 'images/placeholder.png';
  if (!$jsonImages) return $fallback;
  $arr = json_decode($jsonImages, true);
  if (is_array($arr) && !empty($arr) && is_string($arr[0])) return esc($arr[0]);
  return $fallback;
}

function money($n): string {
  if ($n === null || $n === '') return '—';
  return number_format((float)$n, 2);
}

function csrf_token(): string {
  if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
  return $_SESSION['csrf'];
}

function verify_csrf(?string $t): bool {
  return $t && isset($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $t);
}
function sanitize($input) {
  return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
}