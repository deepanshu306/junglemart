<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Quotation Cart - Jungle Mart</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="stylesheet" href="css/navbar.css">
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/cart.css">
  <link rel="stylesheet" href="css/footer.css">
  <!-- GSAP for smooth animations -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
  <script defer src="js/script.js"></script>
  <script defer src="js/cart.js"></script>
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="container cart-page">
  <h1>Your Quotation Cart</h1>
  <p>Review the items you want and send us a <strong>Quotation Request</strong>.</p>

  <div class="cart-body">
    <ul id="cartList"></ul>
    <div id="cartEmpty" class="cart-empty">
      <div class="empty-icon">🛒</div>
      <h3>Your cart is empty</h3>
      <p>Add some products to get started with your quotation request</p>
      <a href="index.php" class="btn primary">Continue Shopping</a>
    </div>

    <div class="cart-summary">
      <div class="summary-item">
        <strong>Total Items:</strong> 
        <span id="cartItemsCount">0</span>
      </div>
      <p class="muted">We will reply with price ranges and MOQ details within 24 hours.</p>
    </div>

    <form id="quoteForm" method="POST" action="send_quotation.php">
      <input type="hidden" name="cart_json" id="cartJsonInput">

      <div class="form-group">
        <div class="form-header">
          <h3>📋 Your Contact Details</h3>
          <p class="form-description">We'll use this information to send you the best quotation and follow up with you</p>
        </div>
        
        <div class="frow">
          <div class="input-group">
            <label for="name">Full Name *</label>
            <input required name="name" type="text" placeholder="Enter your full name" class="form-input" id="name">
            <!-- <span class="input-icon">👤</span> -->
            <div class="input-help">We'll address you by this name</div>
          </div>
          
          <div class="input-group">
            <label for="phone">WhatsApp Number *</label>
            <input required name="phone" type="tel" placeholder="+91 9876543210" class="form-input" id="phone">
            <span class="input-icon">📱</span>
            <div class="input-help">We'll contact you quickly via WhatsApp</div>
          </div>
        </div>
        
        <div class="input-group">
          <label for="email">Email Address *</label>
          <input required name="email" type="email" placeholder="your.email@example.com" class="form-input" id="email">
          <span class="input-icon">✉️</span>
          <div class="input-help">For official quotation documents</div>
        </div>
        
        <div class="input-group">
          <label for="notes">Additional Requirements</label>
          <textarea name="notes" placeholder="Tell us about your specific needs:
• Preferred quantities for each item
• Delivery country/destination
• Special packaging requirements
• Any other special requests" class="form-textarea" id="notes"></textarea>
          <span class="input-icon">📝</span>
          <div class="input-help">The more details you provide, the better we can serve you</div>
        </div>
        
        <div class="form-note">
          <div class="note-icon">💡</div>
          <div class="note-content">
            <strong>Pro Tip:</strong> Include your expected order volume and timeline for the most accurate pricing
          </div>
        </div>
      </div>

      <div class="cart-actions">
        <a id="waButton" class="btn whats" target="_blank" rel="noopener">
          <span class="btn-icon">💬</span>
          Send via WhatsApp
        </a>
        <button type="submit" class="btn email">
          <span class="btn-icon">📧</span>
          Send via Email
        </button>
        <button type="button" class="btn book" id="bookProductsBtn">
          <span class="btn-icon">📦</span>
          Book Your Products
        </button>
      </div>
    </form>
  </div>
</div>

<?php include 'footer.php'; ?>

<script>
const CART_KEY = 'jm_cart';

function loadCart(){ try{return JSON.parse(localStorage.getItem(CART_KEY)||'[]')}catch(e){return[]} }
function saveCart(c){ localStorage.setItem(CART_KEY, JSON.stringify(c)); renderCart(); }
function removeFromCart(id){ saveCart(loadCart().filter(i=>i.id!=id)); }
function renderCart(){
  const c = loadCart();
  const list=document.getElementById('cartList');
  const empty=document.getElementById('cartEmpty');
  const count=document.getElementById('cartItemsCount');
  list.innerHTML='';
  if(c.length===0){ empty.style.display='block'; }
  else {
    empty.style.display='none';
    c.forEach(item=>{
      const li=document.createElement('li');
      li.className='cart-item';
      li.innerHTML=`
        <div class="ci-left">
          <strong>${escapeHtml(item.title)}</strong>
          <div class="ci-meta">MOQ: ${escapeHtml(item.moq||'1')} | Price: ₹${escapeHtml(item.price)}</div>
        </div>
        <div class="ci-right">
          <input type="number" min="1" value="${item.qty}" data-id="${item.id}" class="qty-input">
          <button class="remove-btn" data-id="${item.id}">Remove</button>
        </div>`;
      list.appendChild(li);
    });
  }
  count.textContent=c.reduce((s,i)=>s+parseInt(i.qty||1),0);
  document.getElementById('cartJsonInput').value=JSON.stringify(c);
  prepareWhatsAppLink();
}
function prepareWhatsAppLink(){
  const c=loadCart();
  if(c.length===0){ document.getElementById('waButton').href='#'; return; }
  const WA_NUMBER='YOUR_WHATSAPP_NUMBER'; // Replace with your number
  let text="Hello Jungle Mart,%0AI would like a quotation for:%0A";
  c.forEach(item=>{ text+=`- ${encodeURIComponent(item.title)} (Qty: ${item.qty})%0A`; });
  document.getElementById('waButton').href=`https://wa.me/${WA_NUMBER}?text=${text}`;
}
function escapeHtml(s){return String(s).replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));}
document.addEventListener('click',e=>{
  if(e.target.matches('.remove-btn')) removeFromCart(e.target.dataset.id);
});
document.addEventListener('input',e=>{
  if(e.target.matches('.qty-input')){
    const id=e.target.dataset.id;const val=parseInt(e.target.value)||1;
    const cart=loadCart();const idx=cart.findIndex(i=>i.id==id);
    if(idx>=0){ cart[idx].qty=val; saveCart(cart); }
  }
});
document.addEventListener('DOMContentLoaded',renderCart);
</script>

</body>
</html>