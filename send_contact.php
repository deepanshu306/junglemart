<?php
session_start();
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name']);
    $email   = trim($_POST['email']);
    $phone   = trim($_POST['phone']);
    $message = trim($_POST['message']);

    // Save into DB (optional table: inquiries)
    $stmt = $pdo->prepare("INSERT INTO inquiries (name, email, phone, message, created_at) VALUES (:n,:e,:p,:m,NOW())");
    $stmt->execute([
        ':n'=>$name,
        ':e'=>$email,
        ':p'=>$phone,
        ':m'=>$message
    ]);

    // Send email to admin
    $to = "yourrealemail@example.com"; // change this
    $subject = "New Contact Inquiry from $name";
    $body = "Name: $name\nEmail: $email\nPhone: $phone\n\nMessage:\n$message";
    $headers = "From: noreply@junglemart.com";

    @mail($to, $subject, $body, $headers);

    // Redirect with success
    header("Location: contact.php?status=sent");
    exit;
} else {
    header("Location: contact.php");
    exit;
}
