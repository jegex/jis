import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

gsap.registerPlugin(ScrollTrigger);

export function withReducedMotion(fn) {
  let mm = gsap.matchMedia();

  mm.add('(prefers-reduced-motion: no-preference)', fn);

  return () => mm.revert();
}

export function animateIn(el, opts = {}) {
  const { scrollStart = 'top 85%', ...vars } = opts;

  gsap.from(el, {
    duration: 0.5,
    ease: 'power2.out',
    scrollTrigger: {
      trigger: el,
      start: scrollStart,
      once: true,
    },
    ...vars,
  });
}

export function staggerReveal(container, items, opts = {}) {
  const { scrollStart = 'top 85%', from = {}, to = {}, ...rest } = opts;

  gsap.fromTo(items,
    { y: 30, opacity: 0, ...from },
    {
      y: 0,
      opacity: 1,
      duration: 0.5,
      stagger: 0.08,
      ease: 'power2.out',
      scrollTrigger: {
        trigger: container,
        start: scrollStart,
        once: true,
      },
      ...to,
      ...rest,
    },
  );
}

export function hoverTween(el, enterVars, leaveVars) {
  el.addEventListener('mouseenter', () => gsap.to(el, { overwrite: 'auto', ...enterVars }));

  el.addEventListener('mouseleave', () => gsap.to(el, { overwrite: 'auto', ...leaveVars }));
}

export function progressTween(timeline, opts = {}) {
  const { start = 0, end = 'max' } = opts;

  ScrollTrigger.create({
    start,
    end,
    onUpdate: (self) => {
      if (self.progress > 0) {
        timeline.play();
      } else {
        timeline.reverse();
      }
    },
  });
}
