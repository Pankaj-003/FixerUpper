(function () {
  'use strict';

  const CART_KEY = 'fixerupper_cart_v1';

  function getCart() {
    try {
      const cart = JSON.parse(localStorage.getItem(CART_KEY) || '[]');
      return Array.isArray(cart) ? cart.filter((item) => item && Number(item.id) > 0) : [];
    } catch {
      return [];
    }
  }

  function saveCart(cart) {
    localStorage.setItem(CART_KEY, JSON.stringify(cart));
    updateCartCount();
    window.dispatchEvent(new CustomEvent('fixerupper:cart-updated', { detail: cart }));
  }

  function add(product, quantity = 1) {
    const cart = getCart();
    const productId = Number(product.id);
    const stock = Math.max(0, Number(product.stock) || 0);
    const existing = cart.find((item) => Number(item.id) === productId);

    if (existing) {
      existing.quantity = Math.min(stock, 99, existing.quantity + quantity);
      existing.stock = stock;
    } else {
      cart.push({
        id: productId,
        name: String(product.name),
        description: String(product.description || ''),
        price: Number(product.price),
        stock,
        image_url: String(product.image_url),
        quantity: Math.min(stock, 99, Math.max(1, quantity))
      });
    }
    saveCart(cart);
  }

  function remove(productId) {
    saveCart(getCart().filter((item) => Number(item.id) !== Number(productId)));
  }

  function update(productId, quantity) {
    const cart = getCart();
    const item = cart.find((entry) => Number(entry.id) === Number(productId));
    if (!item) return;
    item.quantity = Math.min(Number(item.stock) || 99, 99, Math.max(1, Number(quantity) || 1));
    saveCart(cart);
  }

  function clear() {
    saveCart([]);
  }

  function count() {
    return getCart().reduce((sum, item) => sum + (Number(item.quantity) || 0), 0);
  }

  function total() {
    return getCart().reduce((sum, item) => sum + Number(item.price) * Number(item.quantity), 0);
  }

  function updateCartCount() {
    const itemCount = count();
    document.querySelectorAll('.cart-count').forEach((badge) => {
      badge.textContent = String(itemCount);
      badge.setAttribute('aria-label', `${itemCount} items in cart`);
    });
  }

  function createQuantityControl(item) {
    const control = document.createElement('div');
    control.className = 'quantity-control';

    const minus = document.createElement('button');
    minus.type = 'button';
    minus.textContent = '−';
    minus.setAttribute('aria-label', `Decrease ${item.name} quantity`);
    minus.addEventListener('click', () => update(item.id, item.quantity - 1));

    const input = document.createElement('input');
    input.type = 'number';
    input.min = '1';
    input.max = String(Math.min(99, Number(item.stock) || 99));
    input.value = String(item.quantity);
    input.setAttribute('aria-label', `${item.name} quantity`);
    input.addEventListener('change', () => update(item.id, input.value));

    const plus = document.createElement('button');
    plus.type = 'button';
    plus.textContent = '+';
    plus.setAttribute('aria-label', `Increase ${item.name} quantity`);
    plus.addEventListener('click', () => update(item.id, item.quantity + 1));

    control.append(minus, input, plus);
    return control;
  }

  function renderCartPage() {
    const itemsContainer = document.getElementById('cart-items');
    if (!itemsContainer) return;

    const cart = getCart();
    const empty = document.getElementById('cart-empty');
    const content = document.getElementById('cart-content');
    const isEmpty = cart.length === 0;
    empty?.classList.toggle('d-none', !isEmpty);
    content?.classList.toggle('d-none', isEmpty);
    itemsContainer.replaceChildren();

    cart.forEach((item) => {
      const row = document.createElement('article');
      row.className = 'cart-item';

      const imageWrap = document.createElement('div');
      imageWrap.className = 'cart-item-image';
      const image = document.createElement('img');
      image.src = item.image_url;
      image.alt = item.name;
      imageWrap.appendChild(image);

      const info = document.createElement('div');
      info.className = 'cart-item-info';
      const title = document.createElement('h3');
      title.textContent = item.name;
      const unit = document.createElement('p');
      unit.textContent = `${window.FixerUpper.formatCurrency(item.price)} each`;
      info.append(title, unit, createQuantityControl(item));

      const price = document.createElement('div');
      price.className = 'cart-item-price';
      const lineTotal = document.createElement('strong');
      lineTotal.textContent = window.FixerUpper.formatCurrency(item.price * item.quantity);
      const removeButton = document.createElement('button');
      removeButton.type = 'button';
      removeButton.className = 'remove-item';
      const removeIcon = document.createElement('i');
      removeIcon.className = 'bi bi-trash3';
      removeIcon.setAttribute('aria-hidden', 'true');
      removeButton.append(removeIcon, document.createTextNode(' Remove'));
      removeButton.addEventListener('click', () => {
        remove(item.id);
        window.FixerUpper.showToast(`${item.name} removed from cart.`);
      });
      price.append(lineTotal, removeButton);
      row.append(imageWrap, info, price);
      itemsContainer.appendChild(row);
    });

    const cartTotal = total();
    const subtotalElement = document.getElementById('cart-subtotal');
    const totalElement = document.getElementById('cart-total');
    if (subtotalElement) subtotalElement.textContent = window.FixerUpper.formatCurrency(cartTotal);
    if (totalElement) totalElement.textContent = window.FixerUpper.formatCurrency(cartTotal);
  }

  document.addEventListener('DOMContentLoaded', () => {
    updateCartCount();
    renderCartPage();
    document.getElementById('clear-cart')?.addEventListener('click', () => {
      clear();
      window.FixerUpper.showToast('Your cart has been cleared.');
    });
  });
  window.addEventListener('fixerupper:cart-updated', renderCartPage);

  window.FixerUpper.Cart = { getCart, add, remove, update, clear, count, total };
}());
