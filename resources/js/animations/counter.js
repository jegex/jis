import { gsap } from 'gsap';
import { withReducedMotion } from './utils.js';

export function initCounter() {
  withReducedMotion(() => {
    const counters = document.querySelectorAll('[data-counter]');

    counters.forEach((el) => {
      const target = parseFloat(el.dataset.counter) || parseFloat(el.textContent) || 0;
      const suffix = el.dataset.suffix || '';
      const prefix = el.dataset.prefix || '';
      const duration = parseFloat(el.dataset.duration) || 2;

      gsap.fromTo(el,
        { textContent: 0 },
        {
          textContent: target,
          duration,
          ease: 'power2.out',
          snap: { textContent: 1 },
          scrollTrigger: {
            trigger: el,
            start: 'top 85%',
            once: true,
          },
          onUpdate: function () {
            const val = Math.round(parseFloat(this.targets()[0].textContent));
            el.textContent = prefix + val.toLocaleString() + suffix;
          },
        },
      );
    });
  });
}
