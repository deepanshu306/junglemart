<?php
require_once __DIR__ . '/includes/db.php';

try {
    $stmt = $pdo->query("SELECT id, name, email, password_hash, is_active FROM admin_users WHERE email = 'admin@junglemart.in' LIMIT 1");
    $user = $stmt->fetch();

    if ($user) {
        echo "Admin User Found:\n";
        echo "ID: " . $user['id'] . "\n";
        echo "Name: " . $user['name'] . "\n";
        echo "Email: " . $user['email'] . "\n";
        echo "Password Hash: " . $user['password_hash'] . "\n";
        echo "Is Active: " . $user['is_active'] . "\n";
    } else {
        echo "Admin user not found.\n";
    }
} catch (Exception $e) {
    echo "Error querying admin user: " . $e->getMessage() . "\n";
}
?>
