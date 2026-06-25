(function () {
  'use strict';

  async function refreshAuthNavigation() {
    let authenticated = false;
    try {
      const result = await window.FixerUpper.apiFetch('/api/auth-status.php');
      authenticated = result?.data?.authenticated === true;
    } catch (error) {
      console.warn('Authentication status could not be checked.');
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
