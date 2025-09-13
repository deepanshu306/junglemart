document.addEventListener('DOMContentLoaded', () => {
  // Thumbnail click → fade swap main image
  const mainImage = document.getElementById('main-product-image');
  const thumbnails = document.querySelectorAll('.thumbnail');

  thumbnails.forEach((thumb) => {
    thumb.addEventListener('click', () => {
      if (thumb.classList.contains('active')) return;
      const newSrc = thumb.dataset.image;
      if (!newSrc) return;

      // Fade out main image
      mainImage.style.opacity = 0;
      setTimeout(() => {
        mainImage.src = newSrc;
        mainImage.style.opacity = 1;
      }, 200);

      // Update active thumbnail
      thumbnails.forEach(t => t.classList.remove('active'));
      thumb.classList.add('active');
    });
  });

  // Lightbox overlay with next/prev if multiple images
  const lightbox = document.querySelector('.lightbox');
  const lightboxImage = document.querySelector('.lightbox-content img');
  const closeBtn = document.querySelector('.lightbox .close');
  const prevBtn = document.querySelector('.lightbox .prev');
  const nextBtn = document.querySelector('.lightbox .next');

  let currentIndex = 0;
  let images = Array.from(thumbnails).map(t => t.dataset.image);

  function showLightbox(index) {
    if (!lightbox) return;
    currentIndex = index;
    lightboxImage.src = images[currentIndex];
    lightbox.classList.add('show');
    updateLightboxButtons();
  }

  function updateLightboxButtons() {
    if (!lightbox) return;
    prevBtn.style.display = currentIndex > 0 ? 'block' : 'none';
    nextBtn.style.display = currentIndex < images.length - 1 ? 'block' : 'none';
  }

  if (mainImage) {
    mainImage.addEventListener('click', () => {
      if (images.length > 0) {
        showLightbox(currentIndex);
      }
    });
  }

  if (closeBtn) {
    closeBtn.addEventListener('click', () => {
      lightbox.classList.remove('show');
    });
  }

  if (prevBtn) {
    prevBtn.addEventListener('click', () => {
      if (currentIndex > 0) {
        currentIndex--;
        lightboxImage.src = images[currentIndex];
        updateLightboxButtons();
      }
    });
  }

  if (nextBtn) {
    nextBtn.addEventListener('click', () => {
      if (currentIndex < images.length - 1) {
        currentIndex++;
        lightboxImage.src = images[currentIndex];
        updateLightboxButtons();
      }
    });
  }

  // Close lightbox on overlay click
  if (lightbox) {
    lightbox.addEventListener('click', (e) => {
      if (e.target === lightbox) {
        lightbox.classList.remove('show');
      }
    });
  }

  // Qty plus/minus with min/max; dispatch custom qtychange event
  const qtyInput = document.getElementById('quantity');
  const btnMinus = document.querySelector('.qty-btn.minus');
  const btnPlus = document.querySelector('.qty-btn.plus');

  function dispatchQtyChange() {
    const event = new CustomEvent('qtychange', { detail: { quantity: parseInt(qtyInput.value) } });
    qtyInput.dispatchEvent(event);
  }

  if (btnMinus && qtyInput) {
    btnMinus.addEventListener('click', () => {
      let val = parseInt(qtyInput.value);
      const min = parseInt(qtyInput.min) || 1;
      if (val > min) {
        qtyInput.value = val - 1;
        dispatchQtyChange();
      }
    });
  }

  if (btnPlus && qtyInput) {
    btnPlus.addEventListener('click', () => {
      let val = parseInt(qtyInput.value);
      const max = parseInt(qtyInput.max) || 9999;
      if (val < max) {
        qtyInput.value = val + 1;
        dispatchQtyChange();
      }
    });
  }

  // Tabs/accordion toggle; remember last open tab in sessionStorage
  const tabButtons = document.querySelectorAll('.tab-btn');
  const tabContents = document.querySelectorAll('.tab-content');

  function activateTab(tabId) {
    tabButtons.forEach(btn => {
      btn.classList.toggle('active', btn.dataset.tab === tabId);
    });
    tabContents.forEach(content => {
      content.classList.toggle('active', content.id === tabId);
    });
    sessionStorage.setItem('activeProductTab', tabId);
  }

  tabButtons.forEach(btn => {
    btn.addEventListener('click', () => {
      activateTab(btn.dataset.tab);
    });
  });

  // On load, activate last open tab or first tab
  const lastTab = sessionStorage.getItem('activeProductTab');
  if (lastTab && document.getElementById(lastTab)) {
    activateTab(lastTab);
  } else if (tabButtons.length > 0) {
    activateTab(tabButtons[0].dataset.tab);
  }
});
