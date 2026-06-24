(function () {
  'use strict';

  document.addEventListener('click', async (event) => {
    const button = event.target.closest('[data-logout]');
    if (!button) return;

    button.disabled = true;
    try {
      await window.FixerUpper.apiFetch('/api/logout.php', { method: 'POST' });
      sessionStorage.removeItem('fixerupper_user');
      sessionStorage.removeItem('fixerupper_last_order');
      window.location.href = 'index.html';
    } catch (error) {
      button.disabled = false;
      window.FixerUpper.showToast(error.message, 'danger');
    }
  });
}());
