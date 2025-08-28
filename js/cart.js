// Simple cart functionality to ensure input fields work
const CART_KEY = 'jm_cart';

// Basic cart functions
function loadCart() {
    try {
        return JSON.parse(localStorage.getItem(CART_KEY) || '[]');
    } catch (e) {
        return [];
    }
}

function saveCart(cart) {
    localStorage.setItem(CART_KEY, JSON.stringify(cart));
    renderCart();
}

function removeFromCart(id) {
    const cart = loadCart().filter(item => item.id != id);
    saveCart(cart);
}

function updateCartQuantity(id, quantity) {
    const cart = loadCart();
    const itemIndex = cart.findIndex(item => item.id == id);
    
    if (itemIndex >= 0) {
        cart[itemIndex].qty = quantity;
        saveCart(cart);
    }
}

// Render cart items
function renderCart() {
    const cart = loadCart();
    const list = document.getElementById('cartList');
    const empty = document.getElementById('cartEmpty');
    const count = document.getElementById('cartItemsCount');
    
    list.innerHTML = '';
    
    if (cart.length === 0) {
        empty.style.display = 'block';
    } else {
        empty.style.display = 'none';
        cart.forEach(item => {
            const li = document.createElement('li');
            li.className = 'cart-item';
            li.innerHTML = `
                <div class="ci-left">
                    <strong>${escapeHtml(item.title)}</strong>
                    <div class="ci-meta">MOQ: ${escapeHtml(item.moq || '1')} | Price: ₹${escapeHtml(item.price)}</div>
                </div>
                <div class="ci-right">
                    <input type="number" min="1" value="${item.qty}" data-id="${item.id}" class="qty-input">
                    <button class="remove-btn" data-id="${item.id}">Remove</button>
                </div>`;
            list.appendChild(li);
        });
    }
    
    count.textContent = cart.reduce((sum, item) => sum + parseInt(item.qty || 1), 0);
    document.getElementById('cartJsonInput').value = JSON.stringify(cart);
    updateWhatsAppLink();
}

// WhatsApp integration
function updateWhatsAppLink() {
    const waButton = document.getElementById('waButton');
    if (!waButton) return;
    
    const cart = loadCart();
    if (cart.length === 0) {
        waButton.href = '#';
        return;
    }
    
    const WA_NUMBER = 'YOUR_WHATSAPP_NUMBER';
    let text = "Hello Jungle Mart,%0AI would like a quotation for:%0A";
    cart.forEach(item => { 
        text += `- ${encodeURIComponent(item.title)} (Qty: ${item.qty})%0A`; 
    });
    waButton.href = `https://wa.me/${WA_NUMBER}?text=${text}`;
}

// Utility functions
function escapeHtml(s) {
    return String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'<','>':'>','"':'"',"'":'&#39;'}[c]));
}

// Event listeners
document.addEventListener('click', e => {
    if (e.target.matches('.remove-btn')) {
        removeFromCart(e.target.dataset.id);
    }
});

document.addEventListener('input', e => {
    if (e.target.matches('.qty-input')) {
        const id = e.target.dataset.id;
        const val = parseInt(e.target.value) || 1;
        updateCartQuantity(id, val);
    }
});

// Initialize cart on page load
document.addEventListener('DOMContentLoaded', renderCart);
