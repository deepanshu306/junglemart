<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

if (is_logged_in()) {
  header('Location: admin_dashboard.php');
  exit;
}
header('Location: admin_login.php');
exit;
