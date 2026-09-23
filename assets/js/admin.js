/**
 * MarocShop - Admin Panel Vanilla JavaScript
 */

document.addEventListener('DOMContentLoaded', () => {
  // 1. Mobile Sidebar Toggle
  const sidebarToggle = document.getElementById('adminSidebarToggle');
  const sidebar = document.getElementById('adminSidebar');

  if (sidebarToggle && sidebar) {
    sidebarToggle.addEventListener('click', () => {
      sidebar.classList.toggle('open');
    });

    document.addEventListener('click', (e) => {
      if (!sidebar.contains(e.target) && !sidebarToggle.contains(e.target) && sidebar.classList.contains('open')) {
        sidebar.classList.remove('open');
      }
    });
  }

  // 2. Image File Upload Preview
  const imageInputs = document.querySelectorAll('.admin-image-upload-input');
  imageInputs.forEach(input => {
    input.addEventListener('change', (e) => {
      const file = e.target.files[0];
      const previewTargetId = input.getAttribute('data-preview');
      const previewImg = document.getElementById(previewTargetId);

      if (file && previewImg) {
        const reader = new FileReader();
        reader.onload = function(evt) {
          previewImg.src = evt.target.result;
          previewImg.style.display = 'block';
        };
        reader.readAsDataURL(file);
      }
    });
  });

  // 3. Confirm Delete Dialogs
  const deleteForms = document.querySelectorAll('.confirm-delete-form');
  deleteForms.forEach(form => {
    form.addEventListener('submit', (e) => {
      const itemTitle = form.getAttribute('data-item') || 'this item';
      if (!confirm(`Are you sure you want to permanently delete ${itemTitle}? This action cannot be undone.`)) {
        e.preventDefault();
      }
    });
  });
});
