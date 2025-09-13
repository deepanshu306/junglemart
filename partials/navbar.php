<header class="navbar">
  <div class="nav-container">
  <a href="index.php" class="logo" style="display: flex; align-items: center; gap: 8px;">
      <img src="images%20and%20logo/IMG_5145.PNG" alt="Jungle Mart Logo" style="height: 120px; width: auto;">
    </a>

<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.11.4/gsap.min.js"></script>
<script src="/js/script.js"></script>

<nav class="nav-links">
    <form action="search.php" method="GET" class="search-form nav-search" id="navSearchForm">
      <input type="text" id="searchInput" name="q" placeholder="Search products..." required class="nav-search-input">
      <button type="button" id="voiceSearchBtn" title="Voice Search" class="nav-search-btn voice-search-btn">
          <img src="images%20and%20logo/microphone.png" alt="Voice Search" class="nav-search-icon">
      </button>
      <input type="file" id="imageSearchInput" accept="image/*" style="display: none;">
      <button type="button" id="imageSearchBtn" title="Image Search" class="nav-search-btn image-search-btn">
          <img src="images%20and%20logo/image-processing.png" alt="Image Search" class="nav-search-icon">
      </button>
      <button type="submit" class="nav-search-btn submit-search-btn">Search</button>
    </form>
  <a href="/index.php">Home</a>
  <a href="pages/categories.php">Categories</a>
  <a href="product.php">Products</a>
  <a href="contact.php">Contact</a>
  <a href="cart.php" class="cart-link">Cart (<span id="cartCount">0</span>)</a>
</nav>

    <div class="nav-toggle" id="navToggle">☰</div>
  </div>
</header>
