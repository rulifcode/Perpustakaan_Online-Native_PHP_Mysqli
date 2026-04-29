document.addEventListener('DOMContentLoaded', function() {
  const toggle = document.querySelector('.mobile-menu-toggle');
  const hamburger = document.querySelector('.hamburger');
  const mobileMenu = document.querySelector('.mobile-menu');
  const overlay = document.querySelector('.mobile-menu-overlay');

  function toggleMenu() {
    hamburger.classList.toggle('active');
    mobileMenu.classList.toggle('active');
    overlay.classList.toggle('active');
    document.body.style.overflow = mobileMenu.classList.contains('active') ? 'hidden' : '';
  }

  if (toggle) {
    toggle.addEventListener('click', toggleMenu);
  }

  if (overlay) {
    overlay.addEventListener('click', toggleMenu);
  }

  document.querySelectorAll('.mobile-menu-list a').forEach(link => {
    link.addEventListener('click', toggleMenu);
  });

  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && mobileMenu.classList.contains('active')) {
      toggleMenu();
    }
  });

  window.addEventListener('resize', function() {
    if (window.innerWidth > 768 && mobileMenu.classList.contains('active')) {
      toggleMenu();
    }
  });
});
// Tutup menu mobile saat klik salah satu link
document.querySelectorAll('.mobile-menu-list a').forEach(link => {
  link.addEventListener('click', () => {
    document.querySelector('.mobile-menu').classList.remove('active');
    document.querySelector('.mobile-menu-overlay').classList.remove('active');
    document.querySelector('.hamburger').classList.remove('active');
  });
});

