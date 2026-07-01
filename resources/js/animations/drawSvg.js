import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { DrawSVGPlugin } from 'gsap/DrawSVGPlugin';

gsap.registerPlugin(ScrollTrigger, DrawSVGPlugin);

export function animateDrawSVG(svg, opts = {}) {
  const {
    loop = true,
    repeatDelay = 1,
    eraseDuration = 0.75,
    drawDuration = 0.75,
    stagger = 0.1,
    scrollTrigger = false,
    initialDraw = '100%',
  } = opts;

  const paths = svg.querySelectorAll('path, circle, rect, ellipse, line, polyline, polygon');
  if (!paths.length) return null;

  gsap.set(paths, { drawSVG: initialDraw });

  const startAnim = () => {
    const tl = gsap.timeline({ repeat: loop ? -1 : 0, repeatDelay });

    tl.to(paths, {
      drawSVG: '0%',
      duration: eraseDuration,
      stagger,
      ease: 'yoyo',
    });

    tl.to(paths, {
      drawSVG: initialDraw,
      duration: drawDuration,
      stagger,
      ease: 'yoyo',
    });

    if (loop) {
      tl.to({}, { duration: repeatDelay });
    }

    return tl;
  };

  if (scrollTrigger) {
    let tl;
    ScrollTrigger.create({
      trigger: svg,
      start: 'top 85%',
      once: true,
      onEnter: () => { tl = startAnim(); },
    });
    return {
      kill: () => tl?.kill(),
    };
  }

  return startAnim();
}

export function initDrawSvg() {
  let mm = gsap.matchMedia();

  mm.add('(prefers-reduced-motion: no-preference)', () => {
    const svgs = document.querySelectorAll('[data-draw-svg]');
    svgs.forEach((svg) => {
      animateDrawSVG(svg, {
        loop: svg.dataset.drawLoop !== undefined,
        repeatDelay: parseFloat(svg.dataset.drawDelay) || 2,
        eraseDuration: parseFloat(svg.dataset.drawEraseDuration) || 0.75,
        drawDuration: parseFloat(svg.dataset.drawDuration) || 0.75,
        stagger: parseFloat(svg.dataset.drawStagger) || 0.1,
        scrollTrigger: svg.dataset.drawScroll !== undefined,
        initialDraw: svg.dataset.drawInitial || '100%',
      });
    });
  });
}
