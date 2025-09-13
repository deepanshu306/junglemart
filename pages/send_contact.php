<?php
session_start();
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    require_once '../includes/helpers.php';  // Ensure sanitize() is loaded

    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $message = sanitize($_POST['message']);
    
    // Here you can add email sending logic
    // For now, just insert into database
    $stmt = $pdo->prepare("INSERT INTO inquiries (name, email, message) VALUES (?, ?, ?)");
    $stmt->execute([$name, $email, $message]);
    
    echo json_encode(['success' => true, 'message' => 'Message sent!']);
} else {
    echo json_encode(['success' => false]);
}
?>
