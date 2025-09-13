document.addEventListener('DOMContentLoaded', function() {
  const form = document.querySelector('.quotation-form');
  if (!form) return;

  form.addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(form);

    // Add cart data from localStorage
    const cartData = localStorage.getItem('jm_cart') || '[]';
    formData.append('cart_data', cartData);

    fetch(form.action, {
      method: form.method,
      body: formData
    })
    .then(response => response.json())
    .then(data => {
      const messageElem = document.createElement('p');
      if (data.success) {
        messageElem.textContent = data.message;
        messageElem.className = 'success-message';
        form.reset();
        localStorage.removeItem('jm_cart');
        // Optionally, refresh cart display or redirect
      } else {
        messageElem.textContent = data.message || 'Failed to send quotation request. Please try again.';
        messageElem.className = 'error-message';
      }
      form.appendChild(messageElem);
    })
    .catch(() => {
      const messageElem = document.createElement('p');
      messageElem.textContent = 'An error occurred. Please try again.';
      messageElem.className = 'error-message';
      form.appendChild(messageElem);
    });
  });
});
