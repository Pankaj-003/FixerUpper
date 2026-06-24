(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('login-form');
    const params = new URLSearchParams(window.location.search);
    const returnPage = window.FixerUpper.safeReturnPage(params.get('return'), 'index.html');
    const registerLink = document.getElementById('register-link');
    if (registerLink) registerLink.href = `register.html?return=${encodeURIComponent(returnPage)}`;

    if (params.get('registered') === '1') {
      window.FixerUpper.showAlert('form-alert', 'Account created successfully. Please log in.', 'success');
    }

    form?.addEventListener('submit', async (event) => {
      event.preventDefault();
      form.classList.add('was-validated');
      if (!form.checkValidity()) return;

      const button = document.getElementById('login-button');
      window.FixerUpper.clearAlert('form-alert');
      window.FixerUpper.setButtonLoading(button, true);

      try {
        const response = await window.FixerUpper.apiFetch('/api/login.php', {
          method: 'POST',
          body: JSON.stringify({
            email: form.email.value.trim(),
            password: form.password.value
          })
        });
        sessionStorage.setItem('fixerupper_user', JSON.stringify(response.data.user));
        window.location.href = returnPage;
      } catch (error) {
        window.FixerUpper.showAlert('form-alert', error.message);
        window.FixerUpper.setButtonLoading(button, false);
      }
    });
  });
}());
