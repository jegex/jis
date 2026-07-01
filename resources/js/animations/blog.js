import { withReducedMotion, animateIn, staggerReveal } from './utils.js';

export function initBlog() {
  withReducedMotion(() => {
    const featuredImg = document.querySelector('[data-blog-image]');
    if (featuredImg) {
      animateIn(featuredImg, {
        scale: 1.1,
        opacity: 0,
        y: 0,
        duration: 0.8,
        scrollStart: 'top 80%',
      });
    }

    const contentParas = document.querySelectorAll('[data-blog-content] > p, [data-blog-content] > h2, [data-blog-content] > h3, [data-blog-content] > blockquote');
    if (contentParas.length) {
      staggerReveal(contentParas[0], contentParas, {
        from: { y: 20 },
        duration: 0.5,
        stagger: 0.08,
        scrollStart: 'top 85%',
      });
    }
  });
}
