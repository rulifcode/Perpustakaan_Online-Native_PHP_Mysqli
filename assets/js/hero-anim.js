document.addEventListener('DOMContentLoaded', () => {
  const text = "Perpustakaan Online";
  const typingText = document.getElementById('typing-text');
  let index = 0;

  function type() {
    if (index <= text.length) {
      typingText.textContent = text.slice(0, index);
      index++;
      setTimeout(type, 120); 
    } else {

      setTimeout(() => {
        index = 0;
        type();
      }, 2000);
    }
  }
  type();

  const heroText = document.querySelector('.hero-text');
  const heroImage = document.querySelector('.hero-image img');

  window.addEventListener('scroll', () => {
    const scrollY = window.scrollY;

    // Parallax 
    heroText.style.transform = `translateY(${scrollY * 0.3}px)`;

    const xMove = Math.sin(scrollY * 0.005) * 10; // goyang 10px max kiri kanan wkkk
    const yMove = Math.cos(scrollY * 0.005) * 5;  // goyang 5px max atas bawah wkwkw
    heroImage.style.transform = `translate(${xMove}px, ${yMove}px)`;
  });
});
