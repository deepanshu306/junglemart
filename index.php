<?php
session_start();
require_once 'db.php';

// fetch categories (limit)
$cats = $pdo->query("SELECT id, name FROM categories WHERE 1 ORDER BY name LIMIT 8")->fetchAll();

// fetch featured
$featured = $pdo->query("
  SELECT id, name, price, wholesale_price, min_order_quantity, images 
  FROM products 
  WHERE is_featured = 1 AND is_active = 1
  LIMIT 8
")->fetchAll();

// recommended
$recommended = $pdo->query("SELECT id, name, price, wholesale_price, min_order_quantity, images FROM products WHERE is_active = 1 ORDER BY RAND() LIMIT 12")->fetchAll();

function first_image($jsonImages) {
  $fallback = 'images/placeholder.png';
  if (empty($jsonImages)) return $fallback;
  $arr = json_decode($jsonImages, true);
  if (is_array($arr) && count($arr) > 0) return htmlspecialchars($arr[0]);
  return $fallback;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Jungle Mart — Quotation Marketplace</title>

  <!-- CSS -->
  <link rel="stylesheet" href="css/navbar.css" />
  <link rel="stylesheet" href="css/style.css" />
  <link rel="stylesheet" href="css/footer.css" />

  <!-- AOS -->
  <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
  <!-- Swiper CSS -->
  <link rel="stylesheet" href="https://unpkg.com/swiper@8/swiper-bundle.min.css" />

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>

<?php include 'navbar.php'; ?>

<!-- Hero -->
<section class="hero-advanced">
  <div class="hero-bg-layer"></div>
  <div class="hero-content container">
    <div class="hero-left" data-aos="fade-right">
      <h1>Source products. Build a quotation. Connect with suppliers.</h1>
      <p class="lead">Request quotations and receive price ranges and MOQs from verified suppliers.</p>

      <form class="hero-search" action="search.php" method="GET" data-aos="zoom-in" data-aos-delay="150">
        <input name="q" type="search" placeholder="Search products, categories, suppliers..." autocomplete="off">
        <button class="btn primary">Search</button>
      </form>

      <div class="hero-trust" data-aos="fade-up" data-aos-delay="250">
        <div class="trust-pill"><strong>Verified Suppliers</strong></div>
        <div class="trust-pill"><strong>Quote in 24-72h</strong></div>
        <div class="trust-pill"><strong>Secure Communication</strong></div>
      </div>
    </div>

    <div class="hero-right" aria-hidden="true">
<img src="images and logo/DSC_0413.JPG" alt="Hero Illustration" class="hero-illustration">
      <div class="floating-leaf leaf-1"></div>
      <div class="floating-leaf leaf-2"></div>
      <div class="floating-leaf leaf-3"></div>
    </div>
  </div>
</section>

<!-- Categories -->
<section class="container categories-strip" data-aos="fade-up">
  <div class="section-head">
    <h2>Categories</h2>
    <p class="muted">Browse our wide range of product categories</p>
  </div>
  <div class="cat-grid">
    <?php foreach($cats as $c): ?>
      <a href="categories.php?id=<?php echo $c['id']; ?>" class="cat-card">
        <div class="cat-svg">📦</div>
        <div class="cat-name"><?php echo htmlspecialchars($c['name']); ?></div>
      </a>
    <?php endforeach; ?>
  </div>
</section>

<!-- Featured products (Swiper) -->
<section class="container section" data-aos="fade-up">
  <div class="section-head">
    <h2>Featured Products</h2>
    <p class="muted">Hand-picked items — add them to your quotation cart.</p>
  </div>

  <div class="swiper featured-swiper">
    <div class="swiper-wrapper">
      <?php foreach($featured as $p): $img = first_image($p['images']); ?>
        <div class="swiper-slide">
          <div class="card product-card hover-3d">
            <img src="<?php echo $img; ?>" alt="<?php echo htmlspecialchars($p['name']); ?>">
            <div class="card-body">
              <h3><?php echo htmlspecialchars($p['name']); ?></h3>
              <p class="price">Price Range: ₹<?php echo number_format($p['price'],2); ?><?php if(!empty($p['wholesale_price'])) echo " - ₹".number_format($p['wholesale_price'],2); ?></p>
              <p class="moq">MOQ: <?php echo (int)$p['min_order_quantity']; ?></p>
              <div class="card-actions">
                <button class="btn add-to-cart" data-id="<?php echo $p['id']; ?>" data-title="<?php echo htmlspecialchars($p['name']); ?>" data-price="<?php echo $p['price']; ?>" data-wholesale="<?php echo $p['wholesale_price']; ?>" data-moq="<?php echo $p['min_order_quantity']; ?>">Add to Cart</button>
                <a class="btn ghost" href="product.php?id=<?php echo $p['id']; ?>">View</a>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    <div class="swiper-button-prev"></div>
    <div class="swiper-button-next"></div>
  </div>
</section>

  <div class="section-head">
    <h2>Recommended for you</h2>
    <p class="muted">Personalized picks — add to your quotation cart.</p>
  </div>

  <div class="grid-reco">
    <?php foreach($recommended as $r): $img = first_image($r['images']); ?>
      <div class="card product-card small hover-3d" data-aos="fade-up">
        <img src="<?php echo $img; ?>" alt="<?php echo htmlspecialchars($r['name']); ?>">
        <div class="card-body">
          <h4><?php echo htmlspecialchars($r['name']); ?></h4>
          <p class="price">₹<?php echo number_format($r['price'],2); ?> <small>MOQ: <?php echo (int)$r['min_order_quantity']; ?></small></p>
          <div class="card-actions">
            <button class="btn add-to-cart" data-id="<?php echo $r['id']; ?>" data-title="<?php echo htmlspecialchars($r['name']); ?>" data-price="<?php echo $r['price']; ?>" data-wholesale="<?php echo $r['wholesale_price']; ?>" data-moq="<?php echo $r['min_order_quantity']; ?>">Add</button>
            <a class="btn ghost" href="product.php?id=<?php echo $r['id']; ?>">View</a>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- Trust -->
<section class="container trust-section" data-aos="fade-up">
  <div class="trust-grid">
    <div class="trust-item">
      <h4>Verified Suppliers</h4>
      <p class="muted">Supplier vetting & profiles</p>
    </div>
    <div class="trust-item">
      <h4>Flexible Price Ranges</h4>
      <p class="muted">Estimate ranges shown on product cards</p>
    </div>
    <div class="trust-item">
      <h4>Quotation Requests</h4>
<p class="muted" style="font-size: 1rem; line-height: 1.6;">Send cart via WhatsApp or Email</p>
    </div>
    <div class="trust-item">
      <h4>Secure Communication</h4>
      <p class="muted">We protect buyer & supplier details</p>
    </div>
  </div>
</section>

<!-- CTA -->
<section class="cta-advanced" data-aos="zoom-in" style="width: 100%;">
  <div class="cta-inner">
    <div>
      <h2>Ready to Request a Quotation?</h2>
      <p class="muted">Collect products into your quotation cart and send your request via WhatsApp or Email.</p>
      <a href="cart.php" class="btn primary lg">Open Quotation Cart</a>
    </div>
    <div aria-hidden="true">
<img src="images and logo/WhatsApp Image 2025-08-19 at 11.24.28_0e8d8639.jpg" alt="Request Quotation" class="cta-graphic">
    </div>
  </div>
</section>

<!-- Flash Sale Section -->
<section class="flash-sale-section" data-aos="fade-up">
  <div class="container">
    <div class="flash-sale-header">
      <h2>🔥 Flash Sale</h2>
      <div class="countdown-timer" id="flashSaleCountdown">24:00:00</div>
    </div>
    <div class="flash-products-grid">
      <!-- Flash sale products will be dynamically loaded here -->
      <div class="flash-product-card">
        <div class="sale-badge">30% OFF</div>
        <img src="images and logo/DSC_0413.JPG" alt="Flash Sale Product">
        <h3>Premium Plant Pots</h3>
        <p class="flash-price">₹1,499 <span class="original-price">₹2,499</span></p>
        <button class="btn primary">Add to Cart</button>
      </div>
      <div class="flash-product-card">
        <div class="sale-badge">25% OFF</div>
        <img src="images and logo/DSC_0413.JPG" alt="Flash Sale Product">
        <h3>Gardening Tools Set</h3>
        <p class="flash-price">₹899 <span class="original-price">₹1,199</span></p>
        <button class="btn primary">Add to Cart</button>
      </div>
      <div class="flash-product-card">
        <div class="sale-badge">40% OFF</div>
        <img src="images and logo/DSC_0413.JPG" alt="Flash Sale Product">
        <h3>Organic Fertilizers</h3>
        <p class="flash-price">₹599 <span class="original-price">₹999</span></p>
        <button class="btn primary">Add to Cart</button>
      </div>
    </div>
  </div>
</section>

<!-- Testimonials Section -->
<section class="testimonials-section" data-aos="fade-up">
  <div class="container">
    <h2>What Our Customers Say</h2>
    <div class="testimonials-grid">
      <div class="testimonial-card">
        <div class="testimonial-content">
          "Jungle Mart helped me find the perfect plants for my restaurant. The suppliers were professional and the prices were competitive!"
        </div>
        <div class="testimonial-author">
          <strong>Rajesh Kumar</strong>
          <span>Restaurant Owner</span>
        </div>
      </div>
      <div class="testimonial-card">
        <div class="testimonial-content">
          "The quotation system is brilliant! I received multiple offers within hours and could choose the best deal for my hotel renovation."
        </div>
        <div class="testimonial-author">
          <strong>Priya Sharma</strong>
          <span>Hotel Manager</span>
        </div>
      </div>
      <div class="testimonial-card">
        <div class="testimonial-content">
          "As a landscape designer, Jungle Mart is my go-to platform for sourcing quality plants at wholesale prices. Highly recommended!"
        </div>
        <div class="testimonial-author">
          <strong>Arun Mehta</strong>
          <span>Landscape Designer</span>
        </div>
      </div>
    </div>
  </div>
</section>
<!-- Blog Section -->
<section class="blog-section" data-aos="fade-up">
  <div class="container">
    <h2>Plant Care Tips & Blog</h2>
    <p class="section-subtitle">Expert advice for plant lovers</p>
    <div class="blog-grid">
      <article class="blog-card">
        <img src="images and logo/DSC_0413.JPG" alt="Plant Care Tips">
        <div class="blog-content">
          <h3>5 Essential Tips for Indoor Plant Care</h3>
          <p>Learn how to keep your indoor plants thriving with these simple tips...</p>
          <a href="#" class="read-more">Read More →</a>
        </div>
      </article>
      <article class="blog-card">
        <img src="images and logo/DSC_0413.JPG" alt="Seasonal Planting">
        <div class="blog-content">
          <h3>Best Plants for Monsoon Season</h3>
          <p>Discover which plants thrive during the rainy season and how to care for them...</p>
          <a href="#" class="read-more">Read More →</a>
        </div>
      </article>
      <article class="blog-card">
        <img src="images and logo/DSC_0413.JPG" alt="Sustainable Gardening">
        <div class="blog-content">
          <h3>Sustainable Gardening Practices</h3>
          <p>Eco-friendly tips for maintaining a beautiful garden while protecting the environment...</p>
          <a href="#" class="read-more">Read More →</a>
        </div>
      </article>
    </div>
  </div>
</section>

<?php include 'footer.php'; ?>

<!-- JS libs -->
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script src="https://unpkg.com/swiper@8/swiper-bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.11.5/gsap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.11.5/ScrollTrigger.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bodymovin/5.7.4/lottie.min.js"></script>

<!-- Your main script -->
<script src="js/script.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
  AOS.init({ once: true, duration: 800 });

  // init swiper
  const swiper = new Swiper('.featured-swiper', {
    slidesPerView: 3,
    spaceBetween: 20,
    navigation: { nextEl: '.swiper-button-next', prevEl: '.swiper-button-prev' },
    breakpoints: {
      320: { slidesPerView: 1 },
      640: { slidesPerView: 2 },
      1000: { slidesPerView: 3 }
    }
  });

  // floating leaf micro animation (GSAP)
  gsap.utils.toArray('.floating-leaf').forEach((el, i) => {
    gsap.to(el, { y: (i+1)*10, x: (i%2?10:-10), repeat: -1, yoyo:true, duration: 4 + i });
  });

  // Lottie
  document.querySelectorAll('.trust-lottie').forEach(el => {
    const src = el.getAttribute('data-lottie');
    if (!src) return;
    lottie.loadAnimation({ container: el, renderer: 'svg', loop: true, autoplay: true, path: src });
  });
});
</script>

</body>
</html>
