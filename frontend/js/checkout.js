(function () {
  'use strict';

  function renderSummary(cart) {
    const container = document.getElementById('checkout-items');
    container.replaceChildren();
    cart.forEach((item) => {
      const row = document.createElement('div');
      row.className = 'checkout-item';
      const image = document.createElement('img');
      image.src = item.image_url;
      image.alt = item.name;
      const info = document.createElement('div');
      const name = document.createElement('strong');
      name.textContent = item.name;
      const quantity = document.createElement('span');
      quantity.textContent = `Quantity: ${item.quantity}`;
      info.append(name, quantity);
      const total = document.createElement('strong');
      total.textContent = window.FixerUpper.formatCurrency(item.price * item.quantity);
      row.append(image, info, total);
      container.appendChild(row);
    });

    const total = window.FixerUpper.Cart.total();
    document.getElementById('checkout-subtotal').textContent = window.FixerUpper.formatCurrency(total);
    document.getElementById('checkout-total').textContent = window.FixerUpper.formatCurrency(total);
  }

  async function initializeCheckout() {
    const loading = document.getElementById('checkout-loading');
    const content = document.getElementById('checkout-content');
    const cart = window.FixerUpper.Cart.getCart();

    if (cart.length === 0) {
      loading.classList.add('d-none');
      window.FixerUpper.showAlert('checkout-alert', 'Your cart is empty. Add a product before checking out.', 'warning');
      window.setTimeout(() => { window.location.href = 'cart.html'; }, 1400);
      return;
    }

    try {
      const authResponse = await window.FixerUpper.apiFetch('/api/auth-status.php');
      if (authResponse?.data?.authenticated !== true) {
        window.location.replace('login.html?return=checkout.html');
        return;
      }

      const productsResponse = await window.FixerUpper.apiFetch('/api/products.php');
      const products = new Map(productsResponse.data.products.map((product) => [Number(product.id), product]));

      const refreshedCart = cart.map((item) => {
        const current = products.get(Number(item.id));
        if (!current || Number(current.stock) < 1) {
          throw new Error(`${item.name} is no longer available.`);
        }
        return {
          ...item,
          name: current.name,
          price: Number(current.price),
          stock: Number(current.stock),
          image_url: current.image_url,
          quantity: Math.min(item.quantity, Number(current.stock))
        };
      });
      localStorage.setItem('fixerupper_cart_v1', JSON.stringify(refreshedCart));
      renderSummary(refreshedCart);

      try {
        const user = JSON.parse(sessionStorage.getItem('fixerupper_user') || 'null');
        if (user?.email) document.getElementById('email').value = user.email;
        if (user?.name) document.getElementById('name').value = user.name;
      } catch { /* Optional convenience data only. */ }

      content.classList.remove('d-none');
    } catch (error) {
      window.FixerUpper.showAlert('checkout-alert', error.message);
    } finally {
      loading.classList.add('d-none');
    }
  }

  document.addEventListener('DOMContentLoaded', () => {
    initializeCheckout();
    const form = document.getElementById('checkout-form');
    form?.addEventListener('submit', async (event) => {
      event.preventDefault();
      form.classList.add('was-validated');
      if (!form.checkValidity()) return;

      const button = document.getElementById('place-order-button');
      window.FixerUpper.clearAlert('checkout-alert');
      window.FixerUpper.setButtonLoading(button, true);

      try {
        const cart = window.FixerUpper.Cart.getCart();
        const response = await window.FixerUpper.apiFetch('/api/checkout.php', {
          method: 'POST',
          body: JSON.stringify({
            items: cart.map((item) => ({
              product_id: Number(item.id),
              quantity: Number(item.quantity)
            })),
            shipping: {
              name: form.name.value.trim(),
              email: form.email.value.trim(),
              phone: form.phone.value.trim(),
              address: form.address.value.trim(),
              city: form.city.value.trim(),
              postal_code: form.postal_code.value.trim()
            }
          })
        });

        const order = response.data.order;
        sessionStorage.setItem('fixerupper_last_order', JSON.stringify(order));
        window.FixerUpper.Cart.clear();
        window.location.href = `order-success.html?order=${encodeURIComponent(order.order_number)}&total=${encodeURIComponent(order.total_amount)}`;
      } catch (error) {
        if (error.status === 401) {
          window.location.replace('login.html?return=checkout.html');
          return;
        }
        window.FixerUpper.showAlert('checkout-alert', error.message);
        window.scrollTo({ top: 0, behavior: 'smooth' });
        window.FixerUpper.setButtonLoading(button, false);
      }
    });
  });
}());
