<?php session_start(); ?>
<header class="navbar">
  <div class="nav-container">
    <a href="index.php" class="logo">🌿 Jungle Mart</a>

<nav class="nav-links">
    <form action="search.php" method="GET" class="search-form" style="display: flex; justify-content: center; align-items: center; margin: 0 auto; max-width: 600px; width: 100%;">
    <input type="text" name="q" placeholder="Search products..." required style="padding: 12px 15px; margin-right: 8px; border-radius: 6px; border: 1px solid #ccc; flex: 1; font-size: 16px;">
    <button type="submit" style="padding: 12px 20px; border-radius: 6px; border: none; background-color: #000000ff; color: white; cursor: pointer; font-size: 16px; font-weight: 500;">Search</button>
  </form>
  <a href="index.php">Home</a>
  <a href="categories.php">Categories</a>
  <a href="cart.php" class="cart-link">Cart (<span id="cartCount">0</span>)</a>
</nav>

    <div class="nav-toggle" id="navToggle">☰</div>
  </div>
</header>
