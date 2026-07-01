import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { ScrollToPlugin } from 'gsap/ScrollToPlugin';
import { withReducedMotion } from './utils.js';

gsap.registerPlugin(ScrollTrigger, ScrollToPlugin);

export function initBackToTop() {
  const btn = document.querySelector('[data-back-to-top]');
  if (!btn) return;

  const ring = btn.querySelector('[data-back-to-top-ring]');
  const tooltip = btn.querySelector('[data-back-to-top-tooltip]');
  const circle = ring?.querySelector('circle:last-child');
  const pathLength = circle?.getTotalLength() ?? 0;

  if (circle) {
    circle.style.strokeDasharray = pathLength;
    circle.style.strokeDashoffset = pathLength;
  }

  withReducedMotion(() => {
    const tl = gsap
      .timeline({ paused: true })
      .set(btn, { visibility: 'visible', pointerEvents: 'none', opacity: 0, scale: 0.8, y: 12 })
      .to(btn, { opacity: 1, scale: 1, y: 0, pointerEvents: 'auto', duration: 0.4, ease: 'backOut(1.7)' });

    ScrollTrigger.create({
      start: 0,
      end: 'max',
      onUpdate: (self) => {
        const progress = self.progress;
        if (circle) {
          circle.style.strokeDashoffset = pathLength * (1 - progress);
        }
        if (progress > 0.05) {
          tl.play();
        } else {
          tl.reverse();
        }
      },
    });
  });

  btn.addEventListener('mouseenter', () => {
    gsap.to(btn, { scale: 1.1, filter: 'drop-shadow(0 0 12px rgba(0,0,128,0.5))', duration: 0.3, ease: 'power2.out' });
    if (tooltip) {
      gsap.to(tooltip, { x: 0, opacity: 1, duration: 0.25, ease: 'power2.out' });
    }
  });

  btn.addEventListener('mouseleave', () => {
    gsap.to(btn, { scale: 1, filter: 'drop-shadow(0 2px 6px rgba(0,0,0,0.15))', duration: 0.25, ease: 'power2.out' });
    if (tooltip) {
      gsap.to(tooltip, { x: 8, opacity: 0, duration: 0.2, ease: 'power2.out' });
    }
  });

  btn.addEventListener('click', () => {
    gsap.to(window, { scrollTo: { y: 0 }, duration: 0.8, ease: 'backOut(1.7)' });
  });
}
