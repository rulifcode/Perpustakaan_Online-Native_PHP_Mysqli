<?php include '../config/config.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Tentang Litera</title>
  <link rel="icon" type="image/png" href="../assets/img/favicon-32x32.png" />
  <link rel="stylesheet" href="../assets/css/tentang.css">
  <link rel="stylesheet" href="../assets/css/header.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <script src="https://unpkg.com/scrollreveal"></script>
  <script src="../assets/js/header.js" defer></script>
    <link rel="stylesheet" href="../assets/css/footer.css">
  <script src="../assets/js/animasi.js" defer></script>
  <script src="../assets/js/scroll.js" defer></script>
 
</head>
<body>
  <?php include '../partials/header_user.php'; ?>
<!-- SEJARAH -->
<section class="sejarah-section">
  <div class="container sejarah-container">
    <div class="sejarah-text">
      <h1>Litera</h1>
      <p>Litera adalah platform perpustakaan digital yang mulai dikembangkan sejak tahun <strong>2025</strong>. Aplikasi ini dirancang oleh mahasiswa <strong>Universitas Sangga Buana YPKP</strong> untuk mendukung literasi modern melalui teknologi.</p>
      <a href="#tim" class="btn-sejarah">Kenali Tim Kami</a>
    </div>
    <div class="sejarah-image">
      <img src="../assets/img/kampus1.jpg" alt="Sejarah Litera">
    </div>
  </div>
</section>

<!-- MISI -->
<section class="parallax mission-section" style="background-image: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('../assets/img/baca2.jpg');">
  <div class="content-box animate__animated animate__fadeInUp">
    <div class="icon-box"><i class="fas fa-book-open"></i></div>
    <h2 class="section-title">Misi Kami</h2>
    <p class="section-description">Membawa buku ke setiap sudut negeri melalui teknologi digital.</p>
    <div class="mission-points">
      <div class="point"><i class="fas fa-mobile-alt"></i><span>Akses literasi digital untuk semua</span></div>
      <div class="point"><i class="fas fa-globe-asia"></i><span>Jangkau seluruh wilayah Indonesia</span></div>
      <div class="point"><i class="fas fa-heart"></i><span>Dibangun dengan passion untuk membaca</span></div>
    </div>
    <a href="#tim" class="cta-button">Pelajari Lebih Lanjut</a>
  </div>
</section>

<!-- VISI -->
<section class="parallax vision-section" style="background-image: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('../assets/img/baca1.jpg');">
  <div class="content-box animate__animated animate__fadeInUp">
    <div class="icon-box"><i class="fas fa-lightbulb"></i></div>
    <h2 class="section-title">Visi Kami</h2>
    <p class="section-description">Menjadi platform literasi digital terbaik yang inklusif dan edukatif.</p>
    <div class="vision-features">
      <div class="feature-card"><h3>Inklusif</h3><p>Untuk semua kalangan tanpa batasan</p></div>
      <div class="feature-card"><h3>Edukatif</h3><p>Konten berkualitas untuk pembelajaran</p></div>
      <div class="feature-card"><h3>Inovatif</h3><p>Terus berkembang dengan teknologi</p></div>
    </div>
  </div>
</section>

<!-- TIM -->
<section class="tim-section" id="tim">
  <div class="container">
    <h2>Tim Kami</h2>
    <div class="tim-grid">
      <div class="tim-card"><img src="../assets/img/3.png" alt="Rulif"><h3>Rulif Fadria Nirwansyah</h3><p>2113241075</p></div>
      <div class="tim-card"><img src="../assets/img/1.png" alt="Febri"><h3>Febriana Abyaz</h3><p>2113241057</p></div>
      <div class="tim-card"><img src="../assets/img/2.png" alt="Arum"><h3>Arum Laras Putri</h3><p>2113241051</p></div>
      <div class="tim-card"><img src="../assets/img/4.png" alt="Syahrizal"><h3>Syahrizal Abdan Rabbani</h3><p>2113241050</p></div>
    </div>
  </div>
</section>

<?php include '../partials/footer.php'; ?>

<!-- ScrollReveal -->
<script>
  ScrollReveal({ distance: '50px', duration: 1000, easing: 'ease-out', reset: false });
  ScrollReveal().reveal('.content-box', { origin: 'bottom', delay: 300 });
  ScrollReveal().reveal('.tim-card', { origin: 'bottom', interval: 200 });

  // Close mobile menu on link click
  document.querySelectorAll('.mobile-menu-list a').forEach(link => {
    link.addEventListener('click', () => {
      document.querySelector('.mobile-menu').classList.remove('active');
      document.querySelector('.mobile-menu-overlay').classList.remove('active');
      document.querySelector('.hamburger').classList.remove('active');
    });
  });
</script>
</body>
</html>
