(function () {
  'use strict';

  class ApiError extends Error {
    constructor(message, status = 0, data = null) {
      super(message);
      this.name = 'ApiError';
      this.status = status;
      this.data = data;
    }
  }

  async function apiFetch(path, options = {}) {
    const controller = new AbortController();
    const timeoutId = window.setTimeout(
      () => controller.abort(),
      window.AppConfig.REQUEST_TIMEOUT_MS
    );
    const headers = new Headers(options.headers || {});
    if (options.body && !headers.has('Content-Type')) {
      headers.set('Content-Type', 'application/json');
    }

    try {
      const response = await fetch(`${window.AppConfig.API_BASE_URL}${path}`, {
        ...options,
        headers,
        credentials: 'include',
        signal: controller.signal
      });
      const data = await response.json().catch(() => null);

      if (!response.ok) {
        throw new ApiError(
          data?.message || `Request failed with status ${response.status}.`,
          response.status,
          data
        );
      }

      return data;
    } catch (error) {
      if (error.name === 'AbortError') {
        throw new ApiError('The server took too long to respond. Please try again.');
      }
      if (error instanceof ApiError) {
        throw error;
      }
      throw new ApiError('Unable to reach the server. Check your connection and API configuration.');
    } finally {
      window.clearTimeout(timeoutId);
    }
  }

  function formatCurrency(value) {
    const amount = Number(value);
    return new Intl.NumberFormat('en-US', {
      style: 'currency',
      currency: 'USD'
    }).format(Number.isFinite(amount) ? amount : 0);
  }

  function showAlert(containerId, message, type = 'danger') {
    const container = document.getElementById(containerId);
    if (!container) return;
    container.replaceChildren();
    const alert = document.createElement('div');
    alert.className = `alert alert-${type}`;
    alert.setAttribute('role', 'alert');
    alert.textContent = message;
    container.appendChild(alert);
  }

  function clearAlert(containerId) {
    document.getElementById(containerId)?.replaceChildren();
  }

  function showToast(message, type = 'success') {
    const container = document.getElementById('toast-container');
    if (!container || !window.bootstrap) return;

    const toast = document.createElement('div');
    toast.className = 'toast';
    toast.setAttribute('role', 'status');
    toast.setAttribute('aria-live', 'polite');

    const header = document.createElement('div');
    header.className = 'toast-header';
    const icon = document.createElement('i');
    icon.className = `bi ${type === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill'} me-2`;
    const title = document.createElement('strong');
    title.className = 'me-auto';
    title.textContent = type === 'success' ? 'FixerUpper' : 'Please check';
    const close = document.createElement('button');
    close.type = 'button';
    close.className = 'btn-close';
    close.dataset.bsDismiss = 'toast';
    close.setAttribute('aria-label', 'Close');
    header.append(icon, title, close);

    const body = document.createElement('div');
    body.className = 'toast-body';
    body.textContent = message;
    toast.append(header, body);
    container.appendChild(toast);

    const instance = new window.bootstrap.Toast(toast, { delay: 3200 });
    toast.addEventListener('hidden.bs.toast', () => toast.remove());
    instance.show();
  }

  function setButtonLoading(button, isLoading) {
    if (!button) return;
    button.disabled = isLoading;
    button.querySelector('.button-label')?.classList.toggle('d-none', isLoading);
    button.querySelector('.spinner-border')?.classList.toggle('d-none', !isLoading);
  }

  function safeReturnPage(value, fallback = 'index.html') {
    const allowed = new Set([
      'index.html', 'products.html', 'cart.html', 'checkout.html',
      'login.html', 'register.html', 'order-success.html'
    ]);
    const page = String(value || '').split('?')[0].split('#')[0];
    return allowed.has(page) ? page : fallback;
  }

  document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-current-year]').forEach((element) => {
      element.textContent = new Date().getFullYear();
    });

    document.querySelectorAll('[data-toggle-password]').forEach((button) => {
      button.addEventListener('click', () => {
        const input = document.getElementById(button.dataset.togglePassword);
        if (!input) return;
        const show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        button.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
        const icon = button.querySelector('i');
        if (icon) icon.className = `bi ${show ? 'bi-eye-slash' : 'bi-eye'}`;
      });
    });
  });

  window.FixerUpper = {
    ApiError,
    apiFetch,
    formatCurrency,
    showAlert,
    clearAlert,
    showToast,
    setButtonLoading,
    safeReturnPage
  };
}());
