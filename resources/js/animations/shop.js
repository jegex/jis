import gsap from 'gsap';
import { animateIn, staggerReveal } from './utils.js';

export function initShop() {
  initProductGrid();
  initAddToCart();
  initCartItems();
  initCartBadge();
  initOrderSuccess();
}

function initProductGrid() {
  const grid = document.querySelector('[data-shop-grid]');
  if (!grid) return;

  const items = grid.querySelectorAll('[data-shop-grid-item]');
  if (!items.length) return;

  staggerReveal(grid, items, {
    scrollStart: 'top 90%',
  });
}

function initAddToCart() {
  const btn = document.querySelector('[data-add-to-cart]');
  if (!btn) return;

  let tl;
  btn.addEventListener('click', () => {
    tl?.kill();
    tl = gsap.timeline();
    tl.to(btn, { scale: 0.92, duration: 0.08, ease: 'power2.in' })
      .to(btn, { scale: 1, duration: 0.35, ease: 'backOut(2.5)' });
  });
}

function initCartItems() {
  const container = document.querySelector('[data-cart-items]');
  if (!container) return;

  const items = container.querySelectorAll('[data-cart-item]');
  if (!items.length) return;

  staggerReveal(container, items, {
    from: { x: -20 },
    to: { x: 0 },
    duration: 0.4,
    stagger: 0.06,
    scrollStart: 'top 90%',
  });
}

function initCartBadge() {
  const badge = document.querySelector('[data-cart-badge]');
  if (!badge) return;

  window.addEventListener('cart-updated', () => {
    gsap.fromTo(
      badge,
      { scale: 1.4 },
      { scale: 1, duration: 0.4, ease: 'backOut(3)' },
    );
  });
}

function initOrderSuccess() {
  const card = document.querySelector('[data-order-success]');
  if (!card) return;

  const tl = gsap.timeline({ defaults: { ease: 'power2.out' } });

  tl.fromTo(card, { scale: 0.85, opacity: 0 }, { scale: 1, opacity: 1, duration: 0.5 })
    .fromTo(
      card.querySelectorAll('[data-order-success-item]'),
      { y: 12, opacity: 0 },
      { y: 0, opacity: 1, duration: 0.3, stagger: 0.04 },
      '-=0.2',
    );
}
