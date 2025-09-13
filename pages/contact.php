<?php
session_start();
require_once '../includes/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Contact - Jungle Mart</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="../css/navbar.css">
  <link rel="stylesheet" href="../css/contact.css">
  <link rel="stylesheet" href="../css/footer.css">
  <script defer src="../js/script.js"></script>
  <script defer src="../js/contact.js"></script>
</head>
<body>

<?php include '../partials/navbar.php'; ?>

<div class="container fade-in">
  <h1>Contact Us</h1>

  <div class="contact-content">
    <div class="contact-info">
      <h2>Get in Touch</h2>
      <p>We'd love to hear from you. Send us a message and we'll respond as soon as possible.</p>
      <div class="contact-details">
        <div class="contact-item">
          <strong>Address:</strong><br>
          JUNGLEMART, CHULIANA, HARAYANA<br>
        </div>
        <div class="contact-item">
          <strong>Phone:</strong><br>
         +91 72060 60607
        </div>
        <div class="contact-item">
          <strong>Email:</strong><br>
          info@junglemart.com
        </div>
        <div class="contact-item">
          <strong>Hours:</strong><br>
          Mon-Fri: 9AM-6PM<br>
          Sat-Sun: 10AM-4PM
        </div>
      </div>
    </div>

    <div class="contact-form">
      <div id="formMessage"></div>

  <form id="contactForm" action="send_contact.php" method="POST">
    <div class="form-group">
      <label for="name">Name</label>
      <input type="text" id="name" name="name" required>
    </div>
    <div class="form-group">
      <label for="email">Email</label>
      <input type="email" id="email" name="email" required>
    </div>
    <div class="form-group">
      <label for="message">Message</label>
      <textarea id="message" name="message" required></textarea>
    </div>
    <button type="submit" class="btn">Send Message</button>
  </form>
    </div>
  </div>
</div>

<?php include '../partials/footer.php'; ?>

</body>
</html>
