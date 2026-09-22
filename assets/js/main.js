// ============================================================
// Portfolio Tokou Zime Maximilien - JavaScript
// ============================================================

const translations = {
  fr: {
    nav_projets: 'Projets',
    nav_competences: 'Compétences',
    nav_contact: 'Contact',
    hero_tag: 'Portfolio Vidéo',
    hero_subtitle: 'Passionné par l\'audiovisuel, la création de contenus multimédias et le storytelling visuel à Cotonou, Bénin.',
    hero_cta_projets: 'Voir les projets',
    hero_cta_contact: 'Me contacter',
    section_profil: 'Mon Profil',
    section_profil_subtitle: 'Qui suis-je et ce que je fais.',
    profil_role: 'Vidéaste · Monteur · Cadreur',
    section_competences: 'Mes Compétences',
    section_competences_subtitle: 'Les outils et savoir-faire que je mets à votre service.',
    section_projets: 'Mes Projets',
    section_projets_subtitle: 'Une sélection de réalisations.',
    filter_all: 'Tout',
    section_contact: 'Me Contacter',
    contact_email: 'Email',
    contact_phone: 'Téléphone',
    contact_location: 'Localisation',
    zoom: 'Agrandir',
    footer: 'Tous droits réservés.',
    see_site: 'Voir le site',
    logout: 'Déconnexion',
    back_home: 'Retour à l\'accueil',
  },
  en: {
    nav_projets: 'Projects',
    nav_competences: 'Skills',
    nav_contact: 'Contact',
    hero_tag: 'Video Portfolio',
    hero_subtitle: 'Passionate about audiovisual, multimedia content and visual storytelling in Cotonou, Benin.',
    hero_cta_projets: 'View Projects',
    hero_cta_contact: 'Contact Me',
    section_profil: 'My Profile',
    section_profil_subtitle: 'Who I am and what I do.',
    profil_role: 'Videographer · Editor · Cameraman',
    section_competences: 'My Skills',
    section_competences_subtitle: 'Tools and expertise at your service.',
    section_projets: 'My Projects',
    section_projets_subtitle: 'A selection of work.',
    filter_all: 'All',
    section_contact: 'Contact Me',
    contact_email: 'Email',
    contact_phone: 'Phone',
    contact_location: 'Location',
    zoom: 'Enlarge',
    footer: 'All rights reserved.',
    see_site: 'View site',
    logout: 'Logout',
    back_home: 'Back home',
  }
};

let currentLang = localStorage.getItem('portfolio-lang') || 'fr';

document.addEventListener('DOMContentLoaded', () => {
  loadTheme();
  applyTranslations();
  updateLangButtons();
  initTypewriter();
  initAnimations();
  initFilters();
  initScrollProgress();
  initBackToTop();
  initLightbox();
  initMobileMenu();
});

// ─── Theme ───
function loadTheme() {
  if (localStorage.getItem('portfolio-theme') === 'light') {
    document.body.classList.add('light-mode');
    updateThemeIcon(true);
  }
}

function toggleTheme() {
  const isLight = document.body.classList.toggle('light-mode');
  localStorage.setItem('portfolio-theme', isLight ? 'light' : 'dark');
  updateThemeIcon(isLight);
}

function updateThemeIcon(isLight) {
  document.querySelectorAll('.theme-toggle i').forEach(icon => {
    icon.className = isLight ? 'fa-solid fa-moon' : 'fa-solid fa-sun';
  });
}

// ─── Language ───
function setLanguage(lang) {
  if (!translations[lang]) return;
  currentLang = lang;
  localStorage.setItem('portfolio-lang', lang);
  applyTranslations();
  updateLangButtons();
}

function applyTranslations() {
  const t = translations[currentLang];
  if (!t) return;
  document.querySelectorAll('[data-i18n]').forEach(el => {
    const key = el.dataset.i18n;
    if (t[key]) el.textContent = t[key];
  });
}

function updateLangButtons() {
  document.querySelectorAll('.lang-switch-item').forEach(btn => {
    btn.classList.toggle('active', btn.dataset.lang === currentLang);
  });
}

// ─── Typewriter ───
function initTypewriter() {
  const el = document.getElementById('typewriter');
  if (!el) return;
  el.textContent = '';
  let i = 0;
  const text = 'Portfolio Vidéo';
  function type() {
    if (i < text.length) {
      el.textContent += text.charAt(i);
      i++;
      setTimeout(type, 80);
    }
  }
  setTimeout(type, 500);
}

// ─── Scroll Animations ───
function initAnimations() {
  const targets = document.querySelectorAll('.project-card, .contact-card, .competence-card, .section-title, .section-subtitle, .filters, .profil-grid');
  if (!('IntersectionObserver' in window)) return;
  targets.forEach(el => el.classList.add('animate-in'));
  const io = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('visible');
        io.unobserve(entry.target);
      }
    });
  }, { threshold: 0.1 });
  targets.forEach(el => io.observe(el));
}

// ─── Scroll Progress Bar ───
function initScrollProgress() {
  const bar = document.createElement('div');
  bar.id = 'scroll-progress';
  bar.style.cssText = 'position:fixed;top:0;left:0;height:2px;background:var(--gradient-primary);z-index:1000;width:0;transition:width 0.1s;';
  document.body.appendChild(bar);
  window.addEventListener('scroll', () => {
    const scrollTop = document.documentElement.scrollTop;
    const scrollHeight = document.documentElement.scrollHeight - document.documentElement.clientHeight;
    const progress = scrollHeight > 0 ? (scrollTop / scrollHeight) * 100 : 0;
    bar.style.width = progress + '%';
  });
}

// ─── Back to Top ───
function initBackToTop() {
  const btn = document.createElement('button');
  btn.className = 'back-to-top';
  btn.innerHTML = '<i class="fa-solid fa-arrow-up"></i>';
  btn.style.cssText = 'position:fixed;bottom:24px;right:24px;width:44px;height:44px;background:var(--bg-surface);border:1px solid var(--border-subtle);border-radius:50%;display:none;align-items:center;justify-content:center;cursor:pointer;z-index:100;color:var(--text-secondary);transition:all 0.3s;box-shadow:var(--shadow-sm);';
  document.body.appendChild(btn);
  btn.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
  window.addEventListener('scroll', () => {
    btn.style.display = window.scrollY > 400 ? 'flex' : 'none';
  });
}

// ─── Filters ───
function initFilters() {
  const filters = document.querySelectorAll('.filter-btn');
  if (!filters.length) return;
  const cards = document.querySelectorAll('.project-card');
  filters.forEach(btn => {
    btn.addEventListener('click', () => {
      filters.forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      const filter = btn.dataset.filter;
      cards.forEach(card => {
        const cat = card.dataset.category;
        if (filter === 'all' || cat === filter) {
          card.style.display = '';
          setTimeout(() => { card.style.opacity = '1'; }, 50);
        } else {
          card.style.display = 'none';
        }
      });
    });
  });
}

// ─── Lightbox ───
function initLightbox() {
  document.addEventListener('click', (e) => {
    const zoomTrigger = e.target.closest('[data-zoom]');
    if (zoomTrigger) {
      e.preventDefault();
      const img = document.querySelector(zoomTrigger.dataset.zoom);
      if (img) {
        const lb = document.getElementById('lightbox');
        const lbImg = document.getElementById('lightbox-img');
        lbImg.src = img.src;
        lb.classList.add('open');
      }
    }
  });
  document.querySelectorAll('.lightbox-backdrop, .lightbox-close').forEach(el => {
    el.addEventListener('click', closeLightbox);
  });
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeLightbox();
  });
}

function closeLightbox() {
  const lb = document.getElementById('lightbox');
  if (lb) lb.classList.remove('open');
}

// ─── Mobile Menu ───
function initMobileMenu() {
  const hamburger = document.getElementById('hamburger');
  const menu = document.getElementById('mobile-menu');
  if (!hamburger || !menu) return;
  hamburger.addEventListener('click', () => {
    menu.classList.toggle('open');
    const isOpen = menu.classList.contains('open');
    hamburger.innerHTML = isOpen
      ? '<i class="fa-solid fa-xmark"></i>'
      : '<i class="fa-solid fa-bars"></i>';
  });
  menu.querySelectorAll('a').forEach(a => {
    a.addEventListener('click', () => {
      menu.classList.remove('open');
      hamburger.innerHTML = '<i class="fa-solid fa-bars"></i>';
    });
  });
}

window.toggleTheme = toggleTheme;
window.setLanguage = setLanguage;