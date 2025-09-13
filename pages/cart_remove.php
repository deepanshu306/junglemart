<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_id'])) {
  $remove_id = $_POST['remove_id'];
  if (isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array_filter($_SESSION['cart'], function($item) use ($remove_id) {
      return $item['id'] != $remove_id;
    });
  }
}
header('Location: cart.php');
exit;
?>
