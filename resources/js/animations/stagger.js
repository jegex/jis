import { withReducedMotion, staggerReveal } from './utils.js';

export function initStagger() {
  withReducedMotion(() => {
    const containers = document.querySelectorAll('[data-stagger]');

    containers.forEach((container) => {
      const items = container.querySelectorAll('[data-stagger-item]');
      if (!items.length) return;

      const staggerDelay = parseFloat(container.dataset.stagger) || 0.08;
      const start = container.dataset.staggerStart || 'top 85%';

      staggerReveal(container, items, {
        stagger: staggerDelay,
        scrollStart: start,
      });
    });
  });
}
