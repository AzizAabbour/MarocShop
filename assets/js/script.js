/**
 * MarocShop - Frontend Vanilla JavaScript
 */

document.addEventListener('DOMContentLoaded', () => {
  // 1. Mobile Menu Drawer
  const hamburgerBtn = document.getElementById('hamburgerBtn');
  const mobileDrawer = document.getElementById('mobileDrawer');
  const mobileOverlay = document.getElementById('mobileOverlay');
  const mobileCloseBtn = document.getElementById('mobileCloseBtn');

  function openMobileMenu() {
    if (mobileDrawer && mobileOverlay) {
      mobileDrawer.classList.add('active');
      mobileOverlay.classList.add('active');
      document.body.style.overflow = 'hidden';
    }
  }

  function closeMobileMenu() {
    if (mobileDrawer && mobileOverlay) {
      mobileDrawer.classList.remove('active');
      mobileOverlay.classList.remove('active');
      document.body.style.overflow = '';
    }
  }

  if (hamburgerBtn) hamburgerBtn.addEventListener('click', openMobileMenu);
  if (mobileCloseBtn) mobileCloseBtn.addEventListener('click', closeMobileMenu);
  if (mobileOverlay) mobileOverlay.addEventListener('click', closeMobileMenu);

  // 2. Search Modal
  const searchModal = document.getElementById('searchModal');
  const searchTriggers = document.querySelectorAll('.search-trigger-btn');
  const searchCloseBtn = document.getElementById('searchCloseBtn');
  const searchInput = document.getElementById('searchModalInput');

  function openSearchModal() {
    if (searchModal) {
      searchModal.classList.add('active');
      if (searchInput) setTimeout(() => searchInput.focus(), 150);
      document.body.style.overflow = 'hidden';
    }
  }

  function closeSearchModal() {
    if (searchModal) {
      searchModal.classList.remove('active');
      document.body.style.overflow = '';
    }
  }

  searchTriggers.forEach(btn => btn.addEventListener('click', (e) => {
    e.preventDefault();
    openSearchModal();
  }));

  if (searchCloseBtn) searchCloseBtn.addEventListener('click', closeSearchModal);
  if (searchModal) {
    searchModal.addEventListener('click', (e) => {
      if (e.target === searchModal) closeSearchModal();
    });
  }

  // Close modals on ESC key
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      closeMobileMenu();
      closeSearchModal();
    }
  });

  // 3. Toast Notification Helper
  window.showToast = function(message, type = 'success') {
    let container = document.querySelector('.toast-container');
    if (!container) {
      container = document.createElement('div');
      container.className = 'toast-container';
      document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    
    const icon = type === 'success' ? '✓' : (type === 'error' ? '✕' : 'ℹ');
    toast.innerHTML = `<span style="color:var(--color-gold); font-weight:bold; font-size:1.2rem;">${icon}</span><span>${message}</span>`;
    
    container.appendChild(toast);

    setTimeout(() => {
      toast.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
      toast.style.opacity = '0';
      toast.style.transform = 'translateY(20px)';
      setTimeout(() => toast.remove(), 400);
    }, 4000);
  };

  // 4. Quantity Buttons (+ / -)
  const qtyWrappers = document.querySelectorAll('.quantity-picker');
  qtyWrappers.forEach(picker => {
    const decBtn = picker.querySelector('.qty-btn.minus');
    const incBtn = picker.querySelector('.qty-btn.plus');
    const input = picker.querySelector('.qty-input');

    if (decBtn && incBtn && input) {
      decBtn.addEventListener('click', () => {
        let val = parseInt(input.value) || 1;
        const min = parseInt(input.getAttribute('min')) || 1;
        if (val > min) {
          input.value = val - 1;
          input.dispatchEvent(new Event('change'));
        }
      });

      incBtn.addEventListener('click', () => {
        let val = parseInt(input.value) || 1;
        const max = parseInt(input.getAttribute('max')) || 999;
        if (val < max) {
          input.value = val + 1;
          input.dispatchEvent(new Event('change'));
        } else {
          window.showToast('Maximum available stock reached.', 'warning');
        }
      });
    }
  });

  // 5. AJAX Add To Cart
  const addToCartForms = document.querySelectorAll('.ajax-add-to-cart');
  addToCartForms.forEach(form => {
    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      const submitBtn = form.querySelector('button[type="submit"]');
      const originalText = submitBtn ? submitBtn.innerHTML : '';
      
      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = 'Adding...';
      }

      const formData = new FormData(form);
      formData.append('ajax', '1');

      try {
        const response = await fetch(form.action || window.location.href, {
          method: 'POST',
          body: formData
        });

        const data = await response.json();
        if (data.success) {
          window.showToast(data.message || 'Product added to cart!', 'success');
          
          // Update cart badges
          const cartBadges = document.querySelectorAll('.cart-count');
          cartBadges.forEach(badge => {
            badge.textContent = data.cart_count;
            badge.style.transform = 'scale(1.3)';
            setTimeout(() => badge.style.transform = 'scale(1)', 200);
          });
        } else {
          window.showToast(data.message || 'Could not add product.', 'error');
        }
      } catch (err) {
        // Fallback: submit standard form
        form.submit();
      } finally {
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.innerHTML = originalText;
        }
      }
    });
  });

  // 6. Thumbnail Image Switcher on Product Page
  const mainProductImg = document.getElementById('mainProductImage');
  const thumbImgs = document.querySelectorAll('.gallery-thumbnail');
  thumbImgs.forEach(thumb => {
    thumb.addEventListener('click', () => {
      if (mainProductImg) {
        mainProductImg.src = thumb.getAttribute('data-full');
        thumbImgs.forEach(t => t.classList.remove('active'));
        thumb.classList.add('active');
      }
    });
  });

  // 7. Auto-submit Cart Quantity change if in Cart table
  const cartQtyInputs = document.querySelectorAll('.cart-table-qty-input');
  cartQtyInputs.forEach(input => {
    input.addEventListener('change', () => {
      const form = input.closest('form');
      if (form) form.submit();
    });
  });
});
