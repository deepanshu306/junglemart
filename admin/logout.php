<?php
// /junglemart/admin/logout.php
require_once __DIR__ . '/../includes/db.php';

// Clear session
session_unset();
session_destroy();

// Redirect to login
header('Location: admin_login.php?msg=Logged+out+successfully');
exit;
