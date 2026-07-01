import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { withReducedMotion, animateIn } from './utils.js';

const ANIMATION_MAP = {
  'fade-up': { y: 40, opacity: 0 },
  'fade-down': { y: -40, opacity: 0 },
  'fade-left': { x: -40, opacity: 0 },
  'fade-right': { x: 40, opacity: 0 },
  'scale-in': { scale: 0.9, opacity: 0 },
  'scale-up': { scale: 0.8, opacity: 0, y: 30 },
};

export function initReveal() {
  withReducedMotion(() => {
    const els = document.querySelectorAll('[data-animate]');

    els.forEach((el) => {
      const type = el.dataset.animate || 'fade-up';
      const delay = parseFloat(el.dataset.delay) || 0;
      const duration = parseFloat(el.dataset.duration) || 0.6;
      const vars = ANIMATION_MAP[type] || ANIMATION_MAP['fade-up'];

      animateIn(el, {
        ...vars,
        delay,
        duration,
        scrollStart: 'top 85%',
      });
    });

    return () => {
      ScrollTrigger.getAll().forEach((st) => st.kill());
    };
  });
}
