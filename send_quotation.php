<?php
session_start();
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $notes = trim($_POST['notes']);
    $cart  = $_POST['cart_json'] ?? '[]';

    // Save RFQ in DB
    $stmt = $pdo->prepare("INSERT INTO rfq (user_id, rfq_data, status, created_at) VALUES (NULL, :data, 'pending', NOW())");
    $stmt->execute([':data' => $cart]);

    // Email to admin
    $to = "yourrealemail@example.com"; // change to your email
    $subject = "New Quotation Request from $name";
    $message = "A new quotation request was submitted.\n\n"
             . "Name: $name\n"
             . "Email: $email\n"
             . "Phone: $phone\n"
             . "Notes: $notes\n\n"
             . "Cart Data:\n$cart";
    $headers = "From: noreply@junglemart.example";

    @mail($to, $subject, $message, $headers);

    // Redirect back with success
    header("Location: index.php?rfq_status=success");
    exit;
} else {
    header("Location: index.php");
    exit;
}
