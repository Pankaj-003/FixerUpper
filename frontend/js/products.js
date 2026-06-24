(function () {
  'use strict';

  let allProducts = [];

  function productCard(product) {
    const column = document.createElement('div');
    column.className = 'col-sm-6 col-lg-4 col-xl-3';

    const card = document.createElement('article');
    card.className = 'product-card';
    const imageWrap = document.createElement('div');
    imageWrap.className = 'product-image-wrap';
    const image = document.createElement('img');
    image.className = 'product-image';
    image.src = product.image_url;
    image.alt = product.name;
    image.loading = 'lazy';
    imageWrap.appendChild(image);

    if (product.featured) {
      const featured = document.createElement('span');
      featured.className = 'product-badge';
      featured.textContent = 'Featured';
      imageWrap.appendChild(featured);
    }
    const stock = document.createElement('span');
    stock.className = 'stock-badge';
    stock.textContent = product.stock > 0 ? `${product.stock} in stock` : 'Out of stock';
    imageWrap.appendChild(stock);

    const body = document.createElement('div');
    body.className = 'product-body';
    const title = document.createElement('h3');
    title.textContent = product.name;
    const description = document.createElement('p');
    description.className = 'product-description';
    description.textContent = product.description;
    const bottom = document.createElement('div');
    bottom.className = 'product-bottom';
    const price = document.createElement('span');
    price.className = 'product-price';
    price.textContent = window.FixerUpper.formatCurrency(product.price);
    const button = document.createElement('button');
    button.type = 'button';
    button.className = 'btn btn-warning';
    button.disabled = product.stock < 1;
    button.setAttribute('aria-label', `Add ${product.name} to cart`);
    const icon = document.createElement('i');
    icon.className = 'bi bi-cart-plus';
    icon.setAttribute('aria-hidden', 'true');
    button.append(icon, document.createTextNode(product.stock > 0 ? ' Add' : ' Sold out'));
    button.addEventListener('click', () => {
      window.FixerUpper.Cart.add(product);
      window.FixerUpper.showToast(`${product.name} added to cart.`);
    });
    bottom.append(price, button);
    body.append(title, description, bottom);
    card.append(imageWrap, body);
    column.appendChild(card);
    return column;
  }

  function render(products) {
    const grid = document.getElementById('product-grid');
    if (!grid) return;
    grid.replaceChildren(...products.map(productCard));
    document.getElementById('product-empty')?.classList.toggle('d-none', products.length > 0);
    const count = document.getElementById('product-count');
    if (count) count.textContent = `${products.length} product${products.length === 1 ? '' : 's'}`;
  }

  async function loadProducts() {
    const grid = document.getElementById('product-grid');
    if (!grid) return;
    const featured = grid.dataset.featured === 'true';

    try {
      const query = featured ? '?featured=true&limit=4' : '';
      const response = await window.FixerUpper.apiFetch(`/api/products.php${query}`);
      allProducts = response.data.products || [];
      render(allProducts);
    } catch (error) {
      window.FixerUpper.showAlert('product-alert', error.message);
    } finally {
      document.getElementById('product-loading')?.classList.add('d-none');
    }
  }

  document.addEventListener('DOMContentLoaded', () => {
    loadProducts();
    document.getElementById('product-search')?.addEventListener('input', (event) => {
      const term = event.target.value.trim().toLowerCase();
      render(allProducts.filter((product) => (
        product.name.toLowerCase().includes(term)
        || product.description.toLowerCase().includes(term)
      )));
    });
  });
}());
