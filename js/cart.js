// ------------------------------
// Jungle Mart - Cart (LocalStorage)
// ------------------------------
const CART_KEY = 'jm_cart';

// -------- Utilities ----------
function esc(s) {
  return String(s).replace(/[&<>"']/g, (c) =>
    ({ '&': '&amp;', '<': '<', '>': '>', '"': '"', "'": '&#39;' }[c])
  );
}
function toInt(v, d = 0) {
  const n = parseInt(v, 10);
  return Number.isFinite(n) ? n : d;
}
function toFloat(v, d = 0) {
  const n = parseFloat(v);
  return Number.isFinite(n) ? n : d;
}
function money(n) {
  const f = toFloat(n, 0);
  return f.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

// -------- Store ----------
function loadCart() {
  try {
    const raw = localStorage.getItem(CART_KEY);
    const parsed = raw ? JSON.parse(raw) : [];
    return Array.isArray(parsed) ? parsed : [];
  } catch {
    return [];
  }
}
function saveCart(cart) {
  localStorage.setItem(CART_KEY, JSON.stringify(cart));
  updateCartBadge();
}

// -------- CRUD ----------
function addToCart({ id, title, price, wholesale, moq, qty = 1 }) {
  id = String(id);
  const cart = loadCart();
  const i = cart.findIndex((x) => String(x.id) === id);

  const minQty = Math.max(1, toInt(moq, 1));
  const addQty = Math.max(minQty, toInt(qty, 1));

  if (i >= 0) {
    cart[i].qty = toInt(cart[i].qty, minQty) + addQty;
  } else {
    cart.push({
      id,
      title: title || `Product #${id}`,
      price: toFloat(price, 0),
      wholesale_price: (wholesale === '' || wholesale === null || typeof wholesale === 'undefined')
        ? null : toFloat(wholesale, null),
      moq: minQty,
      qty: addQty
    });
  }
  saveCart(cart);
  // Update cart count without reloading page to prevent flickering
  updateCartBadge();
}

function removeFromCart(id) {
  const cart = loadCart().filter((item) => String(item.id) !== String(id));
  saveCart(cart);
}

function updateCartQuantity(id, quantity) {
  const cart = loadCart();
  const idx = cart.findIndex((x) => String(x.id) === String(id));
  if (idx < 0) return;

  const minQty = Math.max(1, toInt(cart[idx].moq, 1));
  const q = Math.max(minQty, toInt(quantity, minQty));
  cart[idx].qty = q;
  saveCart(cart);
}

// -------- Sharing Functions ----------
function shareOnWhatsApp() {
  const cart = loadCart();
  if (cart.length === 0) {
    alert('Your cart is empty. Please add some products first.');
    return;
  }

  const lines = cart.map((item) => `- ${item.title} (Qty: ${item.qty})`);
  const text = [
    'Hello Jungle Mart,',
    'I would like a quotation for:',
    ...lines,
    '',
    'Please provide me with pricing details.'
  ].join('\n');

  const whatsappUrl = `https://wa.me/917206060607?text=${encodeURIComponent(text)}`;
  window.open(whatsappUrl, '_blank');
}

function shareViaEmail() {
  const cart = loadCart();
  if (cart.length === 0) {
    alert('Your cart is empty. Please add some products first.');
    return;
  }

  const lines = cart.map((item) => `- ${item.title} (Qty: ${item.qty})`);
  const subject = 'Quotation Request - Jungle Mart';
  const body = [
    'Hello Jungle Mart,',
    '',
    'I would like a quotation for the following products:',
    ...lines,
    '',
    'Please provide me with pricing details and availability.',
    '',
    'Best regards,',
    '[Your Name]'
  ].join('\n');

  const emailUrl = `mailto:info@junglemart.com?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;
  window.location.href = emailUrl;
}

// -------- Form Handling ----------
function handleQuotationSubmit(event) {
  event.preventDefault();

  const form = event.target;
  const formData = new FormData(form);

  // Add cart data to form
  const cart = loadCart();
  formData.append('cart_data', JSON.stringify(cart));

  // Show loading state
  const submitBtn = form.querySelector('button[type="submit"]');
  const originalText = submitBtn ? submitBtn.textContent : 'Submit';
  if (submitBtn) {
    submitBtn.textContent = 'Sending...';
    submitBtn.disabled = true;
  }

  // Submit form
  fetch(form.action, {
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      alert('Thank you! Your quotation request has been sent successfully. We will get back to you within 24 hours.');
      // Clear cart after successful submission
      localStorage.removeItem(CART_KEY);
      // Update cart badge without reloading page to prevent flickering
      updateCartBadge();
      // Optionally redirect to home page or cart page
      // window.location.href = 'index.php';
    } else {
      alert('Sorry, there was an error sending your request. Please try again.');
    }
  })
  .catch(error => {
    console.error('Error:', error);
    alert('Sorry, there was an error sending your request. Please try again.');
  })
  .finally(() => {
    if (submitBtn) {
      submitBtn.textContent = originalText;
      submitBtn.disabled = false;
    }
  });
}

// -------- Navbar badge only ----------
function updateCartBadge() {
  const count = document.getElementById('cartItemsCount');
  if (!count) return;
  const cart = loadCart();
  const totalItems = cart.reduce((sum, it) => sum + Math.max(1, toInt(it.qty, 1)), 0);
  count.textContent = String(totalItems);
}

// -------- Event delegation ----------
document.addEventListener('click', (e) => {
  // Remove from cart
  const rm = e.target.closest('.remove-btn');
  if (rm) {
    e.preventDefault();
    const itemId = rm.closest('.cart-item').getAttribute('data-id');
    removeFromCart(itemId);
    return;
  }

  // Add to cart (from product cards / product page)
  const addBtn = e.target.closest('.add-to-cart');
  if (addBtn) {
    e.preventDefault();
    addToCart({
      id: addBtn.dataset.id,
      title: addBtn.dataset.title,
      price: addBtn.dataset.price,
      wholesale: addBtn.dataset.wholesale,
      moq: addBtn.dataset.moq,
      qty: 1
    });
  }
});

document.addEventListener('input', (e) => {
  const qty = e.target.closest('.qty-input');
  if (qty) {
    const id = qty.dataset.id;
    const val = qty.value;
    updateCartQuantity(id, val);
  }
});

// -------- Cart Rendering (for cart.php page) ----------
function renderCart() {
  const cartList = document.getElementById('cartList');
  const emptyCartMessage = document.getElementById('emptyCartMessage');
  const totalItemsElem = document.getElementById('totalItems');
  const totalPriceElem = document.getElementById('totalPrice');

  if (!cartList || !emptyCartMessage || !totalItemsElem || !totalPriceElem) {
    return; // Not on cart page
  }

  const cart = loadCart();
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
        <h3>${esc(item.title)}</h3>
        <p>Price: ₹${money(item.price)}</p>
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
  totalPriceElem.textContent = `₹${money(totalPrice)}`;
}

// -------- Enhanced Form Validation ----------
function validateQuotationForm(form) {
  const requiredFields = form.querySelectorAll('input[required]');
  let isValid = true;

  requiredFields.forEach(field => {
    if (!field.value.trim()) {
      field.setAttribute('aria-invalid', 'true');
      field.style.borderColor = 'red';
      isValid = false;
    } else {
      field.removeAttribute('aria-invalid');
      field.style.borderColor = '';
    }
  });

  // Email validation
  const emailField = form.querySelector('#customer_email');
  if (emailField && emailField.value) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(emailField.value)) {
      emailField.setAttribute('aria-invalid', 'true');
      emailField.style.borderColor = 'red';
      isValid = false;
    }
  }

  // Phone validation
  const phoneField = form.querySelector('#customer_phone');
  if (phoneField && phoneField.value) {
    const phoneRegex = /^[6-9]\d{9}$/;
    if (!phoneRegex.test(phoneField.value.replace(/\s+/g, ''))) {
      phoneField.setAttribute('aria-invalid', 'true');
      phoneField.style.borderColor = 'red';
      isValid = false;
    }
  }

  return isValid;
}

// -------- Enhanced Form Submission ----------
function handleQuotationSubmit(event) {
  event.preventDefault();

  const form = event.target;

  // Validate form
  if (!validateQuotationForm(form)) {
    alert('Please fill in all required fields correctly.');
    return;
  }

  const cart = loadCart();
  if (cart.length === 0) {
    alert('Your cart is empty. Please add some products first.');
    return;
  }

  const formData = new FormData(form);
  formData.append('cart_data', JSON.stringify(cart));

  // Show loading state
  const submitBtn = form.querySelector('button[type="submit"]');
  const originalText = submitBtn ? submitBtn.textContent : 'Submit';
  if (submitBtn) {
    submitBtn.textContent = 'Sending...';
    submitBtn.disabled = true;
  }

  // Submit form
  fetch(form.action, {
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      alert('Thank you! Your quotation request has been sent successfully. We will get back to you within 24 hours.');
      // Clear cart after successful submission
      localStorage.removeItem(CART_KEY);
      // Update cart badge and re-render
      updateCartBadge();
      renderCart();
      // Reset form
      form.reset();
    } else {
      alert('Sorry, there was an error sending your request. Please try again.');
    }
  })
  .catch(error => {
    console.error('Error:', error);
    alert('Sorry, there was an error sending your request. Please try again.');
  })
  .finally(() => {
    if (submitBtn) {
      submitBtn.textContent = originalText;
      submitBtn.disabled = false;
    }
  });
}

// -------- Init on every page ----------
document.addEventListener('DOMContentLoaded', () => {
  updateCartBadge();   // keep navbar badge in sync

  // Render cart if on cart page
  renderCart();

  // Add form submit handler for quotation form
  const quoteForm = document.querySelector('.quotation-form');
  if (quoteForm) {
    quoteForm.addEventListener('submit', handleQuotationSubmit);
  }

  // Enhanced cart event delegation
  document.addEventListener('input', (e) => {
    const qty = e.target.closest('.qty-input');
    if (qty) {
      const id = qty.dataset.id;
      const val = qty.value;
      updateCartQuantity(id, val);
      // Re-render cart if on cart page
      if (document.getElementById('cartList')) {
        renderCart();
      }
    }
  });

  document.addEventListener('click', (e) => {
    // Remove from cart
    const rm = e.target.closest('.remove-btn');
    if (rm) {
      e.preventDefault();
      const itemId = rm.closest('.cart-item').dataset.id;
      removeFromCart(itemId);
      // Re-render cart if on cart page
      if (document.getElementById('cartList')) {
        renderCart();
      }
    }
  });
});
