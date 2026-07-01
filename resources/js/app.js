import './bootstrap';
import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

gsap.registerPlugin(ScrollTrigger);

import { initReveal } from './animations/reveal.js';
import { initHero } from './animations/hero.js';
import { initCounter } from './animations/counter.js';
import { initNavbar } from './animations/navbar.js';
import { initStagger } from './animations/stagger.js';
import { initBlog } from './animations/blog.js';
import { initDrawSvg } from './animations/drawSvg.js';
import { initHoverCards } from './animations/hoverCard.js';
import { initShop } from './animations/shop.js';
import { initBackToTop } from './animations/backToTop.js';
import { initGallery } from './animations/gallery.js';
const initAll = () => {
    initReveal();
    initHero();
    initCounter();
    initNavbar();
    initStagger();
    initBlog();
    initDrawSvg();
    initHoverCards();
    initShop();
    initBackToTop();
    initGallery();
};

document.addEventListener('DOMContentLoaded', initAll);
