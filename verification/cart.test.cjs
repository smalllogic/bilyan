// Dependency-free DOM fixtures exercise frontend behavior without a browser.
// Visual layout and native browser behavior still require manual review.
const { test } = require('node:test');
const assert = require('node:assert/strict');
const vm = require('node:vm');
const fs = require('node:fs');
const path = require('node:path');
const source = fs.readFileSync(path.join(__dirname, '../bilyan/assets/store.js'), 'utf8');
class Element {
  constructor() { this.children = []; this.events = {}; this.dataset = {}; this.attrs = {}; this.hidden = false; this.disabled = false; this.textContent = ''; this.innerHTML = ''; this.value = ''; this.classList = { add() {}, remove() {}, toggle() {} }; }
  addEventListener(name, fn) { this.events[name] = fn; }
  setAttribute(name, value) { this.attrs[name] = value; }
  getAttribute(name) { return this.attrs[name]; }
  append(...nodes) { this.children.push(...nodes); }
  replaceChildren(...nodes) { this.children = nodes; }
  reportValidity() { return true; }
  focus() { this.focused = true; }
  reset() { this.wasReset = true; }
}
function fixture({ cart = [], checkout = false, fetchImpl } = {}) {
  const nodes = new Map();
  const get = key => { if (!nodes.has(key)) nodes.set(key, new Element()); return nodes.get(key); };
  const storage = new Map([['test-cart', JSON.stringify(cart)]]);
  const listeners = {};
  const document = {
    querySelector: key => key === '#cart-app' && !checkout ? null : get(key),
    querySelectorAll: key => key === '[data-cart-count]' ? [get('count')] : [],
    getElementById: key => get('#' + key), createElement: () => new Element(),
    addEventListener: (name, fn) => { listeners[name] = fn; },
  };
  get('#order-success').hidden = true;
  const requests = [];
  const context = {
    document, console, URLSearchParams, AbortController,
    localStorage: { getItem: key => storage.get(key), setItem: (key, value) => storage.set(key, value) },
    setTimeout: () => 1, clearTimeout() {},
    crypto: { randomUUID: () => '11111111-2222-4333-8444-555555555555' },
    FormData: class { *[Symbol.iterator]() { yield ['name', 'QA Customer']; yield ['consent', '1']; } },
    fetch: async (url, options) => {
      const values = Object.fromEntries(options.body); requests.push(values);
      if (fetchImpl) return fetchImpl(values);
      const items = JSON.parse(values.cart).map(item => ({ ...item, name: 'Test product', available: true, image: '/test.svg', url: '/product', priceLabel: 'USD 49.00', subtotalLabel: 'USD ' + (item.quantity * 49).toFixed(2) }));
      return { ok: true, json: async () => ({ success: true, data: values.action === 'bilyan_cart' ? { items, totalLabel: 'USD ' + items.reduce((sum, i) => sum + i.quantity * 49, 0).toFixed(2), needsQuote: false, nonce: 'test-nonce' } : { reference: 'BLY-TEST-1' } }) };
    },
  };
  context.window = { Bilyan: { ajax: '/ajax', storageKey: 'test-cart' }, crypto: context.crypto, addEventListener() {} };
  vm.runInNewContext(source, context);
  return {
    get, requests, stored: () => JSON.parse(storage.get('test-cart')),
    add: (id, quantity) => { const button = new Element(); button.dataset.add = String(id); if (quantity !== undefined) { button.dataset.quantityInput = 'qty'; get('#qty').value = quantity; } listeners.click({ target: { closest: selector => selector === '[data-add]' ? button : null } }); },
  };
}
const flush = async () => { await new Promise(resolve => setImmediate(resolve)); };
test('Add products, merge quantities and update cart badge', () => {
  const f = fixture(); f.add(12); f.add(12, 3); f.add(13);
  assert.deepEqual(f.stored(), [{ id: 12, quantity: 4 }, { id: 13, quantity: 1 }]);
  assert.equal(f.get('count').textContent, 5);
});
test('Cart survives a new page context', () => {
  const first = fixture(); first.add(12, 3);
  const next = fixture({ cart: first.stored() }); assert.equal(next.get('count').textContent, 3);
});
test('Quantity bounds reject over-limit and fractional additions', () => {
  const f = fixture({ cart: [{ id: 12, quantity: 99 }] }); f.add(12); f.add(13, 1.5); f.add(14, 0);
  assert.deepEqual(f.stored(), [{ id: 12, quantity: 99 }]);
});
test('Damaged cart entries and duplicate IDs are filtered', () => {
  const f = fixture({ cart: [null, { id: 12, quantity: 2 }, { id: 12, quantity: 4 }, { id: -1, quantity: 1 }, { id: 10, quantity: 100 }] });
  assert.equal(f.get('count').textContent, 2);
});
test('Mobile navigation opens with accurate accessibility state', () => {
  const f = fixture(); const menu = f.get('.menu-toggle'); menu.attrs['aria-expanded'] = 'false';
  menu.events.click({ currentTarget: menu }); assert.equal(menu.attrs['aria-expanded'], 'true');
  menu.events.click({ currentTarget: menu }); assert.equal(menu.attrs['aria-expanded'], 'false');
});
test('Checkout displays server totals, refreshes quantity, and removes items', async () => {
  const f = fixture({ checkout: true, cart: [{ id: 12, quantity: 1 }] }); await flush();
  assert.equal(f.get('#cart-total').textContent, 'USD 49.00');
  let row = f.get('#cart-items').children[0];
  row.children[2].children[0].children[2].events.click(); await flush();
  assert.equal(f.stored()[0].quantity, 2); assert.equal(f.get('#cart-total').textContent, 'USD 98.00');
  row = f.get('#cart-items').children[0]; row.children[1].children[2].events.click(); await flush();
  assert.deepEqual(f.stored(), []); assert.equal(f.get('#cart-empty').hidden, false);
});
test('Successful order uses server nonce, displays reference, and clears ordered products', async () => {
  const f = fixture({ checkout: true, cart: [{ id: 12, quantity: 2 }] }); await flush();
  const form = f.get('#order-form'); await form.events.submit({ preventDefault() {}, currentTarget: form });
  assert.equal(f.requests.at(-1).nonce, 'test-nonce');
  assert.equal(f.requests.at(-1).action, 'bilyan_order');
  assert.equal(f.get('#order-reference').textContent, 'BLY-TEST-1');
  assert.equal(f.get('#order-success').hidden, false); assert.deepEqual(f.stored(), []);
});
test('Cart API failure blocks submission and shows a recoverable error', async () => {
  const f = fixture({ checkout: true, cart: [{ id: 12, quantity: 1 }], fetchImpl: () => { throw new Error('Offline'); } }); await flush();
  assert.equal(f.get('#submit-order').disabled, true);
  assert.match(f.get('#cart-error').textContent, /Offline/);
  assert.deepEqual(f.stored(), [{ id: 12, quantity: 1 }]);
});
