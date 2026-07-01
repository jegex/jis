import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { withReducedMotion } from './utils.js';

gsap.registerPlugin(ScrollTrigger);

export function initHero() {
  withReducedMotion(() => {
    const hero = document.querySelector('[data-hero]');
    if (!hero) return;

    const staggerItems = hero.querySelectorAll('[data-hero-stagger]');
    const bg = hero.querySelector('[data-hero-bg]');

    if (staggerItems.length) {
      gsap.from(staggerItems, {
        y: 50,
        opacity: 0,
        scale: 0.95,
        duration: 0.8,
        stagger: 0.15,
        ease: 'power3.out',
        delay: 0.2,
      });
    }

    if (bg) {
      gsap.from(bg, {
        xPercent: 50,
        autoAlpha: 0,
        duration: 0.5,
        ease: 'power3.out',
        delay: 0.2,
      });

      gsap.to(bg, {
        yPercent: -15,
        ease: 'none',
        scrollTrigger: {
          trigger: hero,
          start: 'top top',
          end: 'bottom top',
          scrub: 1,
        },
      });

      gsap.to(bg, {
        rotation: 0.6,
        y: 5,
        duration: 2.5,
        repeat: -1,
        yoyo: true,
        ease: 'sine.inOut',
        delay: 0.8,
      });
    }

    const primaryBtn = hero.querySelector('[data-hero-btn="primary"]');
    if (primaryBtn) {
      const exitIcon = primaryBtn.querySelector('[data-hero-icon="exit"]');
      const enterIcon = primaryBtn.querySelector('[data-hero-icon="enter"]');
      const label = primaryBtn.querySelector('[data-hero-label]');
      const fill = primaryBtn.querySelector('[data-hero-fill]');

      gsap.set(enterIcon, { x: -8, opacity: 0 });

      let fillWidth = 40;

      primaryBtn.addEventListener('mouseenter', () => {
        const btnW = primaryBtn.offsetWidth;
        gsap.to(label, { x: -2, duration: 0.25, ease: 'power2.out' });
        gsap.to(exitIcon, { x: 20, opacity: 0, duration: 0.2, ease: 'power2.out' });
        gsap.to(enterIcon, { x: 0, opacity: 1, duration: 0.3, ease: 'power2.out', delay: 0.1 });
        if (fill) {
          gsap.to(fill, { width: btnW - 8, borderRadius: '0.5rem', duration: 0.3, ease: 'power2.out' });
        }
      });
      primaryBtn.addEventListener('mouseleave', () => {
        gsap.to(label, { x: 0, duration: 0.25, ease: 'power2.out' });
        gsap.to(exitIcon, { x: 0, opacity: 1, duration: 0.3, ease: 'power2.out', delay: 0.1 });
        gsap.to(enterIcon, { x: -20, opacity: 0, duration: 0.2, ease: 'power2.out' });
        if (fill) {
          gsap.to(fill, { width: fillWidth, borderRadius: '0.375rem', duration: 0.3, ease: 'power2.out' });
        }
      });
    }

    const secondaryBtns = hero.querySelectorAll('[data-hero-btn]:not([data-hero-btn="primary"])');
    secondaryBtns.forEach((btn) => {
      const icon = btn.querySelector('svg:last-child');
      const label = btn.querySelector('[data-hero-label]');

      btn.addEventListener('mouseenter', () => {
        gsap.to(icon, { x: 4, duration: 0.25, ease: 'power2.out' });
        if (label) gsap.to(label, { x: -2, duration: 0.25, ease: 'power2.out' });
      });
      btn.addEventListener('mouseleave', () => {
        gsap.to(icon, { x: 0, duration: 0.25, ease: 'power2.out' });
        if (label) gsap.to(label, { x: 0, duration: 0.25, ease: 'power2.out' });
      });
    });
  });
}
