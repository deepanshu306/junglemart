<?php
// Redirect search queries to product.php with query parameters
if (isset($_GET['q']) && trim($_GET['q']) !== '') {
    $q = urlencode(trim($_GET['q']));
    header("Location: product.php?q=$q");
    exit;
} else {
    // If no query, redirect to product.php without parameters
    header("Location: product.php");
    exit;
}
?>
