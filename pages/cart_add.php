<?php
session_start();
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = intval($_POST['id']);
    $title = sanitize($_POST['title']);
    $price = floatval($_POST['price']);
    $quantity = intval($_POST['quantity']);
    
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    $_SESSION['cart'][] = [
        'id' => $id,
        'title' => $title,
        'price' => $price,
        'quantity' => $quantity
    ];
    
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}
?>
