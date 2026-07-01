import { gsap } from 'gsap';
import { hoverTween, progressTween } from './utils.js';

export function initNavbar() {
  const nav = document.querySelector('[data-navbar]');
  if (!nav) return;

  const scrollAnim = gsap.to(nav, {
    backdropFilter: 'blur(12px)',
    boxShadow: '0 1px 3px rgba(0,0,0,0.08)',
    ease: 'none',
    paused: true,
    duration: 0.3,
  });

  progressTween(scrollAnim, { start: 'top -64px', end: 'max' });

  const links = nav.querySelectorAll('[data-nav-hover]');
  links.forEach((link) => {
    hoverTween(link, { scale: 1.05, duration: 0.2, ease: 'power1.out' }, { scale: 1, duration: 0.2, ease: 'power1.out' });
  });
}
