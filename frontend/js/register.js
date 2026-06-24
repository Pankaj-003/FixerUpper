(function () {
  'use strict';

  function passwordIsStrong(password) {
    return password.length >= 10
      && password.length <= 72
      && /[A-Z]/.test(password)
      && /[a-z]/.test(password)
      && /\d/.test(password);
  }

  document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('register-form');
    const params = new URLSearchParams(window.location.search);
    const returnPage = window.FixerUpper.safeReturnPage(params.get('return'), 'index.html');
    const loginLink = document.getElementById('login-link');
    if (loginLink) loginLink.href = `login.html?return=${encodeURIComponent(returnPage)}`;

    form?.addEventListener('submit', async (event) => {
      event.preventDefault();
      const password = form.password.value;
      const confirmation = form.confirm_password.value;
      form.password.setCustomValidity(passwordIsStrong(password) ? '' : 'Password is not strong enough.');
      form.confirm_password.setCustomValidity(password === confirmation ? '' : 'Passwords do not match.');
      form.classList.add('was-validated');
      if (!form.checkValidity()) return;

      const button = document.getElementById('register-button');
      window.FixerUpper.clearAlert('form-alert');
      window.FixerUpper.setButtonLoading(button, true);

      try {
        await window.FixerUpper.apiFetch('/api/register.php', {
          method: 'POST',
          body: JSON.stringify({
            name: form.name.value.trim(),
            email: form.email.value.trim(),
            password
          })
        });
        window.location.href = `login.html?registered=1&return=${encodeURIComponent(returnPage)}`;
      } catch (error) {
        window.FixerUpper.showAlert('form-alert', error.message);
        window.FixerUpper.setButtonLoading(button, false);
      }
    });
  });
}());
