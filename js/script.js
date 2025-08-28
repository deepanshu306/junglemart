// ===============================
// Premium JavaScript Enhancements
// Jungle Mart - Enhanced Animations
// ===============================

document.addEventListener("DOMContentLoaded", function() {
  // Initialize all premium animations and interactions
  initPremiumAnimations();
  initMobileNavigation();
  initSmoothScrolling();
  initLazyLoading();
  initParallaxEffects();
  initCartAnimations();
  initFormAnimations();
  initScrollReveal();
  initPageTransitions();
});

// ===============================
// Premium Animation Initialization
// ===============================

function initPremiumAnimations() {
  // Initialize GSAP animations
  initGSAPAnimations();
  
  // Initialize Lottie animations
  initLottieAnimations();
  
  // Initialize loading animations
  initLoadingAnimations();
}

// ===============================
// Loading Animations
// ===============================

function initLoadingAnimations() {
  const loadingElements = document.querySelectorAll('.loading');
  
  loadingElements.forEach(el => {
    gsap.from(el, {
      duration: 1,
      opacity: 0,
      y: 20,
      ease: 'power2.out',
      scrollTrigger: {
        trigger: el,
        start: 'top 80%',
        toggleActions: 'play none none reverse'
      }
    });
  });
}

// ===============================
// GSAP Animations
// ===============================

function initGSAPAnimations() {
  // Hero section animations
  gsap.from('.hero-left h1', {
    duration: 1.2,
    y: 50,
    opacity: 0,
    ease: 'power3.out',
    delay: 0.3
  });

  gsap.from('.hero-left .lead', {
    duration: 1,
    y: 30,
    opacity: 0,
    ease: 'power2.out',
    delay: 0.6
  });

  gsap.from('.hero-search', {
    duration: 1,
    y: 30,
    opacity: 0,
    ease: 'power2.out',
    delay: 0.9
  });

  gsap.from('.hero-trust', {
    duration: 1,
    y: 30,
    opacity: 0,
    ease: 'power2.out',
    delay: 1.2
  });

  gsap.from('.hero-illustration', {
    duration: 1.5,
    scale: 0.8,
    opacity: 0,
    ease: 'power3.out',
    delay: 0.5
  });

  gsap.from('.floating-leaf', {
    duration: 2,
    scale: 0,
    opacity: 0,
    stagger: 0.3,
    ease: 'elastic.out(1, 0.3)',
    delay: 1
  });

  // Category cards animation - Disabled to prevent disappearing effect

  // Product cards animation
  gsap.from('.product-card', {
    duration: 0.8,
    y: 30,
    opacity: 0,
    stagger: 0.1,
    ease: 'power2.out',
    scrollTrigger: {
      trigger: '.section',
      start: 'top 80%',
      toggleActions: 'play none none reverse'
    }
  });

  // Trust section animation - Disabled to prevent disappearing effect

  // CTA section animation
  gsap.from('.cta-inner > div', {
    duration: 1,
    y: 50,
    opacity: 0,
    stagger: 0.2,
    ease: 'power3.out',
    scrollTrigger: {
      trigger: '.cta-advanced',
      start: 'top 80%',
      toggleActions: 'play none none reverse'
    }
  });
}

// ===============================
// Lottie Animations
// ===============================

function initLottieAnimations() {
  // Initialize Lottie animations for trust icons
  const trustIcons = document.querySelectorAll('.trust-lottie');
  
  trustIcons.forEach(icon => {
    const animationPath = icon.getAttribute('data-lottie');
    if (animationPath) {
      lottie.loadAnimation({
        container: icon,
        renderer: 'svg',
        loop: true,
        autoplay: true,
        path: animationPath
      });
    }
  });

  // Add Lottie animations to buttons
  const primaryButtons = document.querySelectorAll('.btn.primary');
  
  primaryButtons.forEach(button => {
    button.addEventListener('mouseenter', () => {
      // Add subtle scale animation on hover
      gsap.to(button, {
        duration: 0.3,
        scale: 1.05,
        ease: 'power2.out'
      });
    });

    button.addEventListener('mouseleave', () => {
      gsap.to(button, {
        duration: 0.3,
        scale: 1,
        ease: 'power2.out'
      });
    });
  });
}

// ===============================
// Mobile Navigation
// ===============================

function initMobileNavigation() {
  const navToggle = document.getElementById('navToggle');
  const navLinks = document.querySelector('.nav-links');
  
  if (navToggle && navLinks) {
    navToggle.addEventListener('click', () => {
      navLinks.classList.toggle('show');
      
      // Add animation for mobile menu
      if (navLinks.classList.contains('show')) {
        gsap.from('.nav-links a', {
          duration: 0.5,
          x: -30,
          opacity: 0,
          stagger: 0.1,
          ease: 'power3.out'
        });
      }
    });
  }

  // Close mobile menu when clicking outside
  document.addEventListener('click', (e) => {
    if (navLinks && navLinks.classList.contains('show') && 
        !navToggle.contains(e.target) && 
        !navLinks.contains(e.target)) {
      navLinks.classList.remove('show');
    }
  });
}

// ===============================
// Smooth Scrolling
// ===============================

function initSmoothScrolling() {
  const scrollLinks = document.querySelectorAll('a[href^="#"]');
  
  scrollLinks.forEach(link => {
    link.addEventListener('click', (e) => {
      e.preventDefault();
      
      const targetId = link.getAttribute('href');
      const targetElement = document.querySelector(targetId);
      
      if (targetElement) {
        gsap.to(window, {
          duration: 1,
          scrollTo: {
            y: targetElement,
            offsetY: 80
          },
          ease: 'power3.inOut'
        });
      }
    });
  });
}

// ===============================
// Lazy Loading
// ===============================

function initLazyLoading() {
  const lazyImages = document.querySelectorAll('img[data-src]');
  
  const imageObserver = new IntersectionObserver((entries, observer) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const img = entry.target;
        img.src = img.dataset.src;
        img.classList.add('fade-in');
        observer.unobserve(img);
      }
    });
  });

  lazyImages.forEach(img => imageObserver.observe(img));
}

// ===============================
// Parallax Effects
// ===============================

function initParallaxEffects() {
  const parallaxElements = document.querySelectorAll('.parallax');
  
  parallaxElements.forEach(element => {
    gsap.to(element, {
      yPercent: 20,
      ease: 'none',
      scrollTrigger: {
        trigger: element,
        start: 'top bottom',
        end: 'bottom top',
        scrub: true
      }
    });
  });
}

// ===============================
// Cart Animations
// ===============================

function initCartAnimations() {
  // Enhanced add-to-cart functionality with animations
  document.addEventListener("click", e => {
    if (e.target.classList.contains("add-to-cart")) {
      const btn = e.target;
      
      // Add to cart animation
      gsap.fromTo(btn, {
        scale: 1,
        backgroundColor: '#ff6a00'
      }, {
        duration: 0.3,
        scale: 1.1,
        backgroundColor: '#2e7d32',
        ease: 'power2.out',
        onComplete: function() {
          gsap.to(btn, {
            duration: 0.3,
            scale: 1,
            backgroundColor: '#ff6a00'
          });
        }
      });

      // Cart item animation
      const item = {
        id: btn.dataset.id,
        title: btn.dataset.title,
        price: btn.dataset.price,
        wholesale: btn.dataset.wholesale,
        moq: btn.dataset.moq,
        qty: 1
      };
      
      const CART_KEY = 'jm_cart';
      let cart = [];
      try {
        cart = JSON.parse(localStorage.getItem(CART_KEY) || '[]');
      } catch (e) {}
      
      const idx = cart.findIndex(i => i.id == item.id);
      if (idx >= 0) {
        cart[idx].qty++;
      } else {
        cart.push(item);
      }
      
      localStorage.setItem(CART_KEY, JSON.stringify(cart));
      updateCartCount();
      
      // Show success notification
      showNotification('Added to Quotation Cart!', 'success');
    }
  });
}

// ===============================
// Form Animations
// ===============================

function initFormAnimations() {
  const formInputs = document.querySelectorAll('input, textarea, select');
  
  formInputs.forEach(input => {
    // Focus animation
    input.addEventListener('focus', () => {
      gsap.to(input, {
        duration: 0.3,
        scale: 1.02,
        boxShadow: '0 0 0 3px rgba(46, 125, 50, 0.3)',
        ease: 'power2.out'
      });
    });
    
    // Blur animation
    input.addEventListener('blur', () => {
      gsap.to(input, {
        duration: 0.3,
        scale: 1,
        boxShadow: '0 2px 8px rgba(0, 0, 0, 0.1)',
        ease: 'power2.out'
      });
    });
  });
}

// ===============================
// Scroll Reveal
// ===============================

function initScrollReveal() {
  const revealElements = document.querySelectorAll('.reveal-on-scroll');
  
  // Remove the observer to prevent disappearing effect
  revealElements.forEach(element => {
    element.classList.add('visible'); // Ensure elements are always visible
  });
}

// ===============================
// Page Transitions
// ===============================

function initPageTransitions() {
  const links = document.querySelectorAll('a:not([target="_blank"])');
  
  links.forEach(link => {
    link.addEventListener('click', (e) => {
      e.preventDefault();
      const targetUrl = link.getAttribute('href');
      
      // Add page transition animation
      gsap.to(document.body, {
        duration: 0.5,
        opacity: 0,
        onComplete: () => {
          window.location.href = targetUrl;
        }
      });
    });
  });
}

// ===============================
// Update Cart Count
// ===============================

function updateCartCount() {
  const CART_KEY = 'jm_cart';
  let cart = [];
  try { cart = JSON.parse(localStorage.getItem(CART_KEY) || '[]'); } catch (e) {}
  const count = cart.reduce((s, i) => s + parseInt(i.qty || 1), 0);
  const el = document.getElementById("cartCount");
  if (el) el.textContent = count;
}

// ===============================
// Show Notification
// ===============================

function showNotification(message, type) {
  const notification = document.createElement('div');
  notification.className = `notification ${type}`;
  notification.textContent = message;
  document.body.appendChild(notification);
  
  setTimeout(() => {
    notification.remove();
  }, 3000);
}
