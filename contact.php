<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Contact Us - Jungle Mart</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="stylesheet" href="css/navbar.css">
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/footer.css">
  <script defer src="js/script.js"></script>
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="container fade-in">
  <h1>Contact Us</h1>
  <p>Have questions or need help? Reach out to us anytime.</p>

  <div class="contact-page">
    <!-- Contact Info -->
    <div class="contact-info">
      <h3>Get in Touch</h3>
      <p><strong>Email:</strong> infor@junglemart.in</p>
      <p><strong>WhatsApp:</strong> +91 7296060607</p>
      <p><strong>Address:</strong> Jungle Mart, Chuliana, Haryana</p>
    </div>

    <!-- Contact Form -->
    <div class="contact-form">
      <h3>Send Us a Message</h3>
      <form method="POST" action="send_contact.php">
        <input type="text" name="name" placeholder="Your Name" required>
        <input type="email" name="email" placeholder="Your Email" required>
        <input type="tel" name="phone" placeholder="Phone / WhatsApp">
        <textarea name="message" placeholder="Your Message" required></textarea>
        <button type="submit" class="btn email">Send Message</button>
      </form>
    </div>
  </div>
</div>

<?php include 'footer.php'; ?>

<style>
.contact-page {
  display: flex;
  flex-wrap: wrap;
  gap: 30px;
  margin-top: 20px;
}
.contact-info, .contact-form {
  flex: 1 1 45%;
  background: #fff;
  padding: 20px;
  border-radius: 12px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.08);
}
.contact-form form input,
.contact-form form textarea {
  width: 100%;
  padding: 12px;
  margin: 8px 0;
  border: 1px solid #ccc;
  border-radius: 6px;
}
.contact-form form textarea {
  min-height: 120px;
  resize: vertical;
}
</style>

</body>
</html>