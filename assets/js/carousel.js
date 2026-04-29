document.querySelectorAll('.kategori-section').forEach(section => {
  const container = section.querySelector('.carousel-container');
  const btnNext = section.querySelector('.carousel-btn.next');
  const btnPrev = section.querySelector('.carousel-btn.prev');

  btnNext.addEventListener('click', () => {
    container.scrollBy({ left: 250, behavior: 'smooth' });
  });

  btnPrev.addEventListener('click', () => {
    container.scrollBy({ left: -250, behavior: 'smooth' });
  });
});
