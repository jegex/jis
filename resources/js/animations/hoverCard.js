import gsap from 'gsap';
import { hoverTween } from './utils.js';

export function initHoverCards() {
  const cards = document.querySelectorAll('[data-hover-card]');

  cards.forEach((card) => {
    const icon = card.querySelector('[data-hover-icon]');
    const arrow = card.querySelector('[data-hover-arrow]');

    hoverTween(
      card,
      {
        y: -6,
        boxShadow: '0 20px 40px -12px rgba(0,0,0,0.15)',
        duration: 0.35,
        ease: 'power2.out',
      },
      {
        y: 0,
        boxShadow: 'none',
        duration: 0.3,
        ease: 'power2.inOut',
      },
    );

    if (icon) {
      hoverTween(
        icon,
        { scale: 1.15, rotate: -4, duration: 0.4, ease: 'backOut(1.7)' },
        { scale: 1, rotate: 0, duration: 0.35, ease: 'power2.inOut' },
      );
    }

    if (arrow) {
      hoverTween(
        arrow,
        { x: 4, duration: 0.3, ease: 'power2.out' },
        { x: 0, duration: 0.25, ease: 'power2.inOut' },
      );
    }
  });
}
