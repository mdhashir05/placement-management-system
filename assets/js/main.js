/**
 * Main Application Script
 * Theme Toggling, Form Validations, Spinner & UI Utilities
 */

document.addEventListener('DOMContentLoaded', () => {
  // Theme Toggle Persistence
  const savedTheme = localStorage.getItem('app-theme') || 'light';
  if (savedTheme === 'dark') {
    document.body.classList.add('dark-mode');
  }

  const themeBtn = document.getElementById('theme-toggle-btn');
  if (themeBtn) {
    themeBtn.addEventListener('click', () => {
      document.body.classList.toggle('dark-mode');
      const isDark = document.body.classList.contains('dark-mode');
      localStorage.setItem('app-theme', isDark ? 'dark' : 'light');
      themeBtn.innerHTML = isDark ? '<i class="fas fa-sun text-warning"></i>' : '<i class="fas fa-moon text-secondary"></i>';
    });
  }

  // Sidebar Toggle for Mobile
  const sidebarToggle = document.getElementById('sidebar-toggle');
  const sidebar = document.querySelector('.app-sidebar');
  if (sidebarToggle && sidebar) {
    sidebarToggle.addEventListener('click', () => {
      sidebar.classList.toggle('show');
    });
  }

  // HTML5 Form Validation Enhancement
  const forms = document.querySelectorAll('.needs-validation');
  Array.from(forms).forEach(form => {
    form.addEventListener('submit', event => {
      if (!form.checkValidity()) {
        event.preventDefault();
        event.stopPropagation();
      } else {
        showSpinner();
      }
      form.classList.add('was-validated');
    }, false);
  });
});

/**
 * Global Loading Spinner Helpers
 */
function showSpinner() {
  const spinner = document.getElementById('loading-spinner');
  if (spinner) spinner.classList.add('show');
}

function hideSpinner() {
  const spinner = document.getElementById('loading-spinner');
  if (spinner) spinner.classList.remove('show');
}

/**
 * Toast Notification Helper
 */
function showToast(message, type = 'success') {
  const toastContainer = document.getElementById('toast-container');
  if (!toastContainer) return;

  const bgClass = type === 'success' ? 'bg-success' : (type === 'danger' ? 'bg-danger' : 'bg-primary');
  const toastHtml = `
    <div class="toast align-items-center text-white ${bgClass} border-0 mb-2 shadow" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="d-flex">
        <div class="toast-body">${message}</div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
    </div>
  `;
  
  toastContainer.insertAdjacentHTML('beforeend', toastHtml);
  const newToastEl = toastContainer.lastElementChild;
  const bsToast = new bootstrap.Toast(newToastEl, { delay: 4000 });
  bsToast.show();
}
