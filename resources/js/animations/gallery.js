import gsap from 'gsap';

export function initGallery() {
  const gallery = document.querySelector('[data-gallery]');
  if (!gallery) return;

  const mainImage = gallery.querySelector('[x-ref="mainImage"]');
  if (!mainImage) return;

  window.addEventListener('gallery-swap', () => {
    const img = mainImage.querySelector('img');
    if (!img) return;

    gsap.fromTo(
      img,
      { opacity: 0.4, scale: 0.98 },
      { opacity: 1, scale: 1, duration: 0.35, ease: 'power2.out', clearProps: 'scale' },
    );
  });
}
