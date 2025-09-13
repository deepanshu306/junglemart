document.addEventListener('DOMContentLoaded', function() {
  const form = document.getElementById('contactForm');
  const formMessage = document.getElementById('formMessage');

  if (!form) return;

  form.addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(form);

    fetch(form.action, {
      method: form.method,
      body: formData
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        formMessage.textContent = data.message;
        formMessage.className = 'success-message';
        form.reset();
      } else {
        formMessage.textContent = 'Failed to send message. Please try again.';
        formMessage.className = 'error-message';
      }
    })
    .catch(() => {
      formMessage.textContent = 'An error occurred. Please try again.';
      formMessage.className = 'error-message';
    });
  });
});
