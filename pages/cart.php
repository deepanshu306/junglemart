<?php
session_start();
require_once '../includes/db.php';

$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
$total = 0;
$totalItems = 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Cart - Jungle Mart</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="../css/navbar.css">
  <link rel="stylesheet" href="../css/cart.css">
  <link rel="stylesheet" href="../css/footer.css">
  <script defer src="../js/script.js"></script>
  <script defer src="../js/cart.js"></script>
  <script defer src="../js/quotation.js"></script>
</head>
<body>

<?php include '../partials/navbar.php'; ?>

<main class="cart-page">
  <h1>Your Shopping Cart</h1>
  <p>Review your selected products and request a quotation.</p>

  <section class="cart-body">
    <div class="cart-section" aria-label="Shopping Cart Items">
      <h2>Cart Items</h2>
      <ul id="cartList" class="cart-items" role="list" aria-live="polite" aria-relevant="additions removals">
        <!-- Cart items will be rendered here by JavaScript -->
      </ul>
      <div id="emptyCartMessage" class="cart-empty" style="display:none;" role="alert" aria-live="assertive">
        <div class="empty-icon" aria-hidden="true">🛒</div>
        <h3>Your cart is empty</h3>
        <p>Add some products to your cart to see them here.</p>
      </div>
    </div>

    <aside class="details-section" aria-label="Cart Summary and Quotation Form">
      <div class="cart-summary" aria-live="polite" aria-atomic="true">
        <p>Total Items: <span id="totalItems">0</span></p>
        <p>Total Price: <span id="totalPrice">₹0.00</span></p>
      </div>

      <form class="quotation-form" action="send_quotation.php" method="POST" novalidate aria-label="Quotation Request Form">
        <h2>Request a Quotation</h2>

        <div class="form-group">
          <label for="customer_name">Name <span aria-hidden="true">*</span></label>
          <input type="text" id="customer_name" name="customer_name" required aria-required="true" autocomplete="name" />
        </div>

        <div class="form-group">
          <label for="customer_email">Email <span aria-hidden="true">*</span></label>
          <input type="email" id="customer_email" name="customer_email" required aria-required="true" autocomplete="email" />
        </div>

        <div class="form-group">
          <label for="customer_phone">Phone <span aria-hidden="true">*</span></label>
          <input type="tel" id="customer_phone" name="customer_phone" required aria-required="true" autocomplete="tel" />
        </div>

        <div class="form-group">
          <label for="company">Company (optional)</label>
          <input type="text" id="company" name="company" autocomplete="organization" />
        </div>

        <div class="form-group">
          <label for="requirements">Requirements (optional)</label>
          <textarea id="requirements" name="requirements" rows="4"></textarea>
        </div>

        <button type="submit" class="btn book">Send Quotation Request</button>
      </form>
    </aside>
  </section>
</main>

<?php include '../partials/footer.php'; ?>

<script>
  // Render cart items and update totals
  function renderCart() {
    const cartList = document.getElementById('cartList');
    const emptyCartMessage = document.getElementById('emptyCartMessage');
    const totalItemsElem = document.getElementById('totalItems');
    const totalPriceElem = document.getElementById('totalPrice');

    const cart = JSON.parse(localStorage.getItem('jm_cart') || '[]');
    cartList.innerHTML = '';

    if (cart.length === 0) {
      emptyCartMessage.style.display = 'block';
      totalItemsElem.textContent = '0';
      totalPriceElem.textContent = '₹0.00';
      return;
    } else {
      emptyCartMessage.style.display = 'none';
    }

    let totalItems = 0;
    let totalPrice = 0;

    cart.forEach(item => {
      totalItems += item.qty;
      totalPrice += item.price * item.qty;

      const li = document.createElement('li');
      li.className = 'cart-item';
      li.setAttribute('data-id', item.id);
      li.setAttribute('role', 'listitem');

      li.innerHTML = `
        <div class="item-details">
          <h3>${item.title}</h3>
          <p>Price: ₹${item.price.toFixed(2)}</p>
          <p>MOQ: ${item.moq}</p>
        </div>
        <div class="item-actions">
          <input type="number" min="${item.moq}" value="${item.qty}" class="qty-input" data-id="${item.id}" aria-label="Quantity for ${item.title}" />
          <button class="remove-btn" aria-label="Remove ${item.title} from cart">Remove</button>
        </div>
      `;

      cartList.appendChild(li);
    });

    totalItemsElem.textContent = totalItems;
    totalPriceElem.textContent = `₹${totalPrice.toFixed(2)}`;
  }

  // Initial render
  document.addEventListener('DOMContentLoaded', () => {
    renderCart();

    // Listen for quantity changes and remove button clicks delegated in cart.js
    document.getElementById('cartList').addEventListener('input', (e) => {
      if (e.target.classList.contains('qty-input')) {
        const id = e.target.dataset.id;
        const val = e.target.value;
        updateCartQuantity(id, val);
        renderCart();
      }
    });

    document.getElementById('cartList').addEventListener('click', (e) => {
      if (e.target.classList.contains('remove-btn')) {
        const id = e.target.closest('.cart-item').dataset.id;
        removeFromCart(id);
        renderCart();
      }
    });
  });
</script>

