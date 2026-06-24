(function () {
  'use strict';

  async function refreshAuthNavigation() {
    let authenticated = false;
    try {
      const result = await window.FixerUpper.apiFetch('/api/orders.php?limit=1');
      authenticated = result?.data?.authenticated === true;
    } catch (error) {
      if (error.status !== 401) {
        console.warn('Authentication status could not be checked.');
      }
    }

    document.querySelectorAll('.auth-guest').forEach((element) => {
      element.classList.toggle('d-none', authenticated);
    });
    document.querySelectorAll('.auth-user').forEach((element) => {
      element.classList.toggle('d-none', !authenticated);
    });
  }

  document.addEventListener('DOMContentLoaded', refreshAuthNavigation);
  window.FixerUpper.refreshAuthNavigation = refreshAuthNavigation;
}());
