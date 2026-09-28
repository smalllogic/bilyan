(() => {
  'use strict';
  const config = window.Bilyan;
  if (!config) return;
  const $ = (selector, root = document) => root.querySelector(selector);
  let memory = [];
  let storageAvailable = true;
  let revision = 0;
  let nonce = '';
  let sending = false;
  let requestId = newRequestId();
  function newRequestId() { return window.crypto?.randomUUID ? crypto.randomUUID() : Date.now().toString(36) + '-' + Math.random().toString(36).slice(2) + '-' + Math.random().toString(36).slice(2); }
  function readCart() {
    try {
      const value = storageAvailable ? JSON.parse(localStorage.getItem(config.storageKey) || '[]') : memory;
      if (!Array.isArray(value)) return [];
      const seen = new Set();
      return value.filter(item => item && Number.isInteger(item.id) && item.id > 0 && Number.isInteger(item.quantity) && item.quantity > 0 && item.quantity <= 99 && !seen.has(item.id) && seen.add(item.id)).slice(0, 50);
    } catch { storageAvailable = false; return memory; }
  }
  function writeCart(cart) {
    memory = cart;
    try { localStorage.setItem(config.storageKey, JSON.stringify(cart)); }
    catch { storageAvailable = false; toast('Your browser cannot save this cart. Keep this page open until you send your request.'); }
    countCart();
  }
  function countCart() { document.querySelectorAll('[data-cart-count]').forEach(el => { el.textContent = readCart().reduce((sum, item) => sum + item.quantity, 0); }); }
  let toastTimer;
  function toast(message) { const el = $('#toast'); el.textContent = message; el.classList.add('visible'); clearTimeout(toastTimer); toastTimer = setTimeout(() => el.classList.remove('visible'), 4200); }
  function showCartModal(button, quantity) {
    const modal = $('#cart-modal');
    if (!modal || typeof modal.querySelector !== 'function') return;
    const card = button.closest?.('.product-card');
    const detail = button.closest?.('.product-detail');
    const image = card ? $('img', card.querySelector('.product-image')) : (detail ? $('.detail-image img', detail) : null);
    const title = card ? $('.product-info h3', card) : (detail ? $('h1', detail) : null);
    const modalImage = $('#cart-modal-image');
    if (image && modalImage) { modalImage.src = image.currentSrc || image.src; modalImage.alt = image.alt || ''; }
    if (title) $('#cart-modal-product-name').textContent = title.textContent.trim();
    $('#cart-modal-quantity').textContent = 'Quantity ' + quantity;
    modal.hidden = false;
    if (document.body?.classList) document.body.classList.add('modal-open');
    $('[data-cart-modal-close]', modal)?.focus();
  }
  function closeCartModal() { const modal = $('#cart-modal'); if (modal) { modal.hidden = true; if (document.body?.classList) document.body.classList.remove('modal-open'); } }
  async function api(action, values) {
    const controller = new AbortController();
    const timeout = setTimeout(() => controller.abort(), 25000);
    try {
      const response = await fetch(config.ajax, { method: 'POST', credentials: 'same-origin', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: new URLSearchParams({ action, ...values }), signal: controller.signal });
      let result;
      try { result = await response.json(); } catch { throw new Error('The server could not respond. Please try again, or contact us by email.'); }
      if (!response.ok || !result.success) throw new Error(result.data?.message || 'Please refresh the page and try again.');
      return result.data;
    } catch (error) { if (error.name === 'AbortError') throw new Error('The connection timed out. Please try again; the same request will not be saved twice.'); throw error; }
    finally { clearTimeout(timeout); }
  }
  document.addEventListener('click', event => {
    if (event.target.closest('[data-cart-modal-close]')) { closeCartModal(); return; }
    const button = event.target.closest('[data-add]');
    if (!button || button.disabled) return;
    const input = button.dataset.quantityInput ? document.getElementById(button.dataset.quantityInput) : null;
    if (input && !input.reportValidity()) return;
    const quantity = input ? Number(input.value) : 1;
    if (!Number.isInteger(quantity) || quantity < 1 || quantity > 99) return toast('Choose a quantity between 1 and 99.');
    const id = Number(button.dataset.add);
    const cart = readCart(); const existing = cart.find(item => item.id === id);
    if (existing && existing.quantity + quantity > 99) return toast('You can request up to 99 of each product.');
    if (!existing && cart.length >= 50) return toast('Your cart can contain up to 50 different products.');
    if (existing) existing.quantity += quantity; else cart.push({ id, quantity });
    writeCart(cart); showCartModal(button, quantity);
    button.classList.add('added'); setTimeout(() => button.classList.remove('added'), 600);
  });
  document.addEventListener('keydown', event => { if (event.key === 'Escape') closeCartModal(); });
  $('.menu-toggle')?.addEventListener('click', event => { const button = event.currentTarget; const open = button.getAttribute('aria-expanded') !== 'true'; button.setAttribute('aria-expanded', String(open)); $('#site-nav').classList.toggle('open', open); });
  countCart();
  window.addEventListener('storage', event => { if (event.key === config.storageKey) { countCart(); if ($('#cart-app') && !sending) refreshCart(); } });
  if (!$('#cart-app')) return;
  function element(tag, className, text) { const el = document.createElement(tag); if (className) el.className = className; if (text !== undefined) el.textContent = text; return el; }
  function errorMessage(target, message) { const el = $(target); el.textContent = message; el.hidden = !message; }
  function changeQuantity(id, quantity) {
    if (sending) return;
    const cart = readCart(); const item = cart.find(row => row.id === id);
    if (!item) return;
    item.quantity = Math.min(99, Math.max(1, Number.isFinite(quantity) ? Math.floor(quantity) : 1));
    writeCart(cart); requestId = newRequestId(); refreshCart();
  }
  function renderLine(item) {
    const row = element('article', 'cart-item');
    const image = element('img', 'cart-item-image'); image.src = item.image || ''; image.alt = item.name; image.width = 88; image.height = 88;
    if (item.image) row.append(image); else row.append(element('div', 'cart-item-image'));
    const info = element('div', 'cart-item-info');
    const name = element(item.available ? 'a' : 'span', 'cart-item-name', item.name); if (item.available) name.href = item.url;
    info.append(name, element('small', '', item.available ? item.priceLabel : 'Remove this item before sending your order.'));
    const remove = element('button', 'remove-item', 'Remove'); remove.type = 'button'; remove.disabled = sending; remove.setAttribute('aria-label', 'Remove ' + item.name);
    remove.addEventListener('click', () => { writeCart(readCart().filter(row => row.id !== item.id)); requestId = newRequestId(); refreshCart(); }); info.append(remove); row.append(info);
    const controls = element('div', 'cart-item-controls'); const stepper = element('div', 'quantity-stepper');
    const minus = element('button', '', '−'); minus.type = 'button'; minus.disabled = sending || item.quantity <= 1; minus.setAttribute('aria-label', 'Decrease quantity of ' + item.name);
    const input = element('input'); input.type = 'number'; input.min = '1'; input.max = '99'; input.value = item.quantity; input.disabled = sending; input.setAttribute('aria-label', 'Quantity of ' + item.name);
    const plus = element('button', '', '+'); plus.type = 'button'; plus.disabled = sending || item.quantity >= 99; plus.setAttribute('aria-label', 'Increase quantity of ' + item.name);
    minus.addEventListener('click', () => changeQuantity(item.id, Number(input.value) - 1)); plus.addEventListener('click', () => changeQuantity(item.id, Number(input.value) + 1)); input.addEventListener('change', () => changeQuantity(item.id, Number(input.value)));
    stepper.append(minus, input, plus); controls.append(stepper, element('strong', '', item.available ? item.subtotalLabel : 'Unavailable')); row.append(controls); return row;
  }
  async function refreshCart() {
    const current = ++revision;
    errorMessage('#cart-error', '');
    const cart = readCart();
    $('#cart-empty').hidden = cart.length > 0; $('#cart-content').hidden = cart.length === 0;
    $('#submit-order').disabled = true;
    if (!cart.length) { $('#cart-loading').hidden = true; return; }
    $('#cart-loading').hidden = false;
    try {
      const data = await api('bilyan_cart', { cart: JSON.stringify(cart) });
      if (current !== revision) return;
      nonce = data.nonce;
      $('#cart-items').replaceChildren(...data.items.map(renderLine));
      $('#cart-total').textContent = data.totalLabel; $('#cart-quote').hidden = !data.needsQuote;
      $('#submit-order').disabled = sending || data.items.some(item => !item.available);
    } catch (error) { if (current === revision) errorMessage('#cart-error', error.message + ' Reload this page to retry.'); }
    finally { if (current === revision) $('#cart-loading').hidden = true; }
  }
  $('#order-form').addEventListener('submit', async event => {
    event.preventDefault();
    if (sending || $('#submit-order').disabled || !event.currentTarget.reportValidity()) return;
    sending = true;
    const form = event.currentTarget;
    const button = $('#submit-order'); const old = button.innerHTML;
    const cartSnapshot = readCart();
    const values = Object.fromEntries(new FormData(form));
    document.querySelectorAll('#cart-items button, #cart-items input, #order-form input, #order-form textarea, #submit-order').forEach(el => { el.disabled = true; });
    button.textContent = 'Sending your request…'; errorMessage('#order-error', '');
    try {
      const result = await api('bilyan_order', { ...values, cart: JSON.stringify(cartSnapshot), nonce, request_id: requestId });
      // Preserve products added in another tab while this request was in flight.
      const remaining = readCart().map(item => ({ ...item, quantity: item.quantity - (cartSnapshot.find(old => old.id === item.id)?.quantity || 0) })).filter(item => item.quantity > 0);
      writeCart(remaining); $('#cart-content').hidden = true; $('#cart-empty').hidden = true;
      $('#order-reference').textContent = result.reference; $('#order-success').hidden = false; $('#order-success').focus();
      requestId = newRequestId(); form.reset();
    } catch (error) { errorMessage('#order-error', error.message); }
    finally {
      sending = false; button.innerHTML = old;
      document.querySelectorAll('#order-form input, #order-form textarea').forEach(el => { el.disabled = false; });
      if ($('#order-success').hidden) refreshCart();
    }
  });
  refreshCart();
})();
