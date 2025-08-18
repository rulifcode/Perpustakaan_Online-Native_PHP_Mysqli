<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="assets/img/favicon-32x32.png" />
  <title>Litera - Perpustakaan Online</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/header.css">
  <link rel="stylesheet" href="assets/css/footer.css">
  <script src="assets/js/header.js"></script>
  <script src="assets/js/animasi.js" defer></script>
  <script src="assets/js/scroll.js" defer></script>
  <script>
    document.documentElement.classList.add('js-enabled');
  </script>
</head>
<style>
    /* Hero section - Modified */
    .hero {
      background: linear-gradient(180deg, #e9f3fc 0%, #d0e3fc 100%);
      padding: 80px 0 60px;
      overflow: hidden;
      min-height: 70vh;
      display: flex;
      align-items: center;
    }

    .hero-content {
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: 40px;
    }

    .hero-text {
      max-width: 600px;
      position: relative;
      z-index: 2;
      will-change: transform;
      transition: transform 0.2s ease-out;
    }

    .hero-text h1 {
      font-size: 48px;
      font-weight: 900;
      margin-bottom: 20px;
      line-height: 1.2;
      color: #1f3c88;
      text-shadow: 1px 1px 4px rgba(44, 102, 184, 0.3);
    }

    #typing-text {
      border-right: 3px solid #2c66b8;
      white-space: nowrap;
      overflow: hidden;
      display: inline-block;
      font-weight: 800;
      font-size: 48px;
      color: #1f3c88;
      animation: blinkCaret 1.2s step-end infinite;
    }

    @keyframes blinkCaret {
      0%, 50%, 100% { border-color: #2c66b8; }
      25%, 75% { border-color: transparent; }
    }

    .hero-subtitle {
      font-size: 22px;
      margin-bottom: 30px;
      color: #344e89;
      line-height: 1.6;
      font-weight: 600;
    }

    .hero-description {
      font-size: 18px;
      margin-bottom: 40px;
      color: #5a6b8a;
      line-height: 1.7;
      max-width: 500px;
    }

    .hero-buttons {
      display: flex;
      gap: 20px;
      flex-wrap: wrap;
      align-items: center;
    }

    .btn-primary {
      background: linear-gradient(135deg, #2c66b8, #1a4580);
      color: white;
      padding: 16px 32px;
      border: none;
      border-radius: 12px;
      font-size: 16px;
      font-weight: 700;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      cursor: pointer;
      transition: all 0.3s ease;
      box-shadow: 0 6px 20px rgba(44, 102, 184, 0.3);
    }

    .btn-primary:hover {
      transform: translateY(-3px);
      box-shadow: 0 12px 30px rgba(44, 102, 184, 0.4);
      background: linear-gradient(135deg, #1a4580, #0d2c5a);
    }

    .btn-secondary {
      background: transparent;
      color: #2c66b8;
      padding: 16px 32px;
      border: 2px solid #2c66b8;
      border-radius: 12px;
      font-size: 16px;
      font-weight: 600;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .btn-secondary:hover {
      background: #2c66b8;
      color: white;
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgba(44, 102, 184, 0.3);
    }

    .hero-stats {
      display: flex;
      gap: 30px;
      margin-top: 40px;
      flex-wrap: wrap;
    }

    .stat-item {
      text-align: center;
    }

    .stat-number {
      font-size: 32px;
      font-weight: 900;
      color: #2c66b8;
      display: block;
      line-height: 1;
    }

    .stat-label {
      font-size: 14px;
      color: #5a6b8a;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-top: 5px;
    }

    .hero-image {
      flex-shrink: 0;
    }

    .hero-image img {
      max-width: 420px;
      width: 100%;
      border-radius: 20px;
      box-shadow: 0 20px 60px rgba(44, 102, 184, 0.2);
      transition: transform 0.4s ease-in-out, box-shadow 0.4s ease;
      will-change: transform;
      cursor: pointer;
    }

    .hero-image img:hover {
      transform: scale(1.05) rotate(1deg);
      box-shadow: 0 30px 80px rgba(44, 102, 184, 0.3);
    }

    /* Floating animation */
    .floating {
      animation: floating 6s ease-in-out infinite;
    }

    @keyframes floating {
      0%, 100% { transform: translateY(0px); }
      50% { transform: translateY(-20px); }
    }

    /* Rest of your existing styles for other sections */
    .buku-section {
      padding: 50px 0;
      background-color: #f9f9f9;
    }

    .buku-section h2 {
      text-align: center;
      font-size: 1.8rem;
      margin-bottom: 30px;
      color: #333;
    }

    .buku-scroll-wrapper {
      overflow: hidden;
      position: relative;
    }

    .buku-scroll {
      display: flex;
      gap: 1rem;
      animation: scrollBuku 60s linear infinite;
    }

    .buku-card {
      flex: 0 0 auto;
      width: 200px;
      padding: 1rem;
      background: #fff;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
      text-align: center;
      transition: transform 0.3s;
    }

    .buku-card img {
      width: 100%;
      height: 200px;
      object-fit: cover;
      border-radius: 8px;
    }

    @keyframes scrollBuku {
      0% { transform: translateX(0); }
      100% { transform: translateX(-50%); }
    }

    .edukasi-section {
      padding: 60px 20px;
      background-color: #fff;
      text-align: center;
    }

    .edukasi-section h2 {
      color: #1f3c88;
      margin-bottom: 40px;
      font-size: 32px;
      font-weight: 900;
      letter-spacing: 1.2px;
    }

    .edukasi-content {
      display: flex;
      gap: 28px;
      max-width: 900px;
      margin: 0 auto;
      flex-wrap: wrap;
      justify-content: center;
    }

    .edukasi-content article {
      flex: 1 1 300px;
      background-color: #e9f3fc;
      border-radius: 16px;
      padding: 20px;
      box-shadow: 0 6px 18px rgba(31, 60, 136, 0.12);
      text-align: left;
      transition: transform 0.4s ease, box-shadow 0.3s ease;
    }

    .edukasi-content article:hover {
      transform: translateY(-14px);
      box-shadow: 0 14px 35px rgba(114, 56, 2, 0.25);
    }

    .edukasi-content h3 {
      color:rgb(8, 20, 52);
      margin-bottom: 14px;
      font-weight: 800;
      font-size: 20px;
    }

    .edukasi-content p {
      color: #444;
      font-size: 15px;
      line-height: 1.55;
    }

    /* Mobile Responsive */
    @media screen and (max-width: 768px) {
      .container {
        padding: 10px 15px;
      }

      .hero {
        padding: 40px 0 30px;
        min-height: 60vh;
      }

      .hero-content {
        flex-direction: column;
        text-align: center;
        gap: 30px;
      }

      .hero-text {
        max-width: 100%;
      }

      .hero-text h1, #typing-text {
        font-size: 32px;
      }

      .hero-subtitle {
        font-size: 18px;
        margin-bottom: 20px;
      }

      .hero-description {
        font-size: 16px;
        margin-bottom: 30px;
        max-width: 100%;
      }

      .hero-buttons {
        justify-content: center;
        gap: 15px;
      }

      .btn-primary, .btn-secondary {
        padding: 14px 24px;
        font-size: 14px;
      }

      .hero-stats {
        justify-content: center;
        gap: 20px;
        margin-top: 30px;
      }

      .stat-number {
        font-size: 24px;
      }

      .hero-image img {
        max-width: 300px;
      }

      .nav-list {
        flex-direction: column;
        gap: 10px;
      }

      .header-content {
        flex-direction: column;
        gap: 15px;
      }
    }

    @media screen and (max-width: 480px) {
      .hero-text h1, #typing-text {
        font-size: 28px;
      }

      .hero-subtitle {
        font-size: 16px;
      }

      .hero-description {
        font-size: 15px;
      }

      .hero-buttons {
        flex-direction: column;
        width: 100%;
      }

      .btn-primary, .btn-secondary {
        width: 100%;
        justify-content: center;
      }

      .hero-stats {
        gap: 15px;
      }

      .stat-number {
        font-size: 20px;
      }

      .hero-image img {
        max-width: 250px;
      }
    }

    @media screen and (max-width: 768px) {
  .hero-buttons {
    flex-direction: column;
    align-items: center;
    width: 100%;
  }

  .hero-buttons a {
    width: 90%;
    text-align: center;
  }
}

@media screen and (max-width: 480px) {
  .hero-buttons a {
    font-size: 14px;
    padding: 12px 20px;
  }
}

</style>
<body>
    <?php include 'config/config.php'; ?>
<?php include 'partials/header.php'; ?>
   <section id="beranda" class="hero">
    <div class="container hero-content">
      <div class="hero-text">
        <h1><span id="typing-text">Selamat Datang di Litera</span></h1>
        <p class="hero-description">
          Temukan ribuan koleksi buku digital terbaik dari berbagai genre. 
          Mulai petualangan membacamu bersama Litera dan rasakan pengalaman 
          membaca yang tak terlupakan.
        </p>
        
        <div class="hero-buttons">
          <a href="#buku-terbaru" class="btn-primary">
            📚 Jelajahi Koleksi
          </a>
          <a href="auth/login.php" class="btn-secondary">
            Daftar Sekarang?
          </a>
        </div>

        <div class="hero-stats">
          <div class="stat-item">
            <span class="stat-number">1000+</span>
            <span class="stat-label">Koleksi Buku</span>
          </div>
          <div class="stat-item">
            <span class="stat-number">500+</span>
            <span class="stat-label">Pembaca Aktif</span>
          </div>
          <div class="stat-item">
            <span class="stat-number">50+</span>
            <span class="stat-label">Kategori</span>
          </div>
        </div>
      </div>
      
      <div class="hero-image">
        <img src="assets/img/Buku-cerita-anak.png" alt="Ilustrasi Buku" class="floating" />
      </div>
    </div>
  </section>

<section id="buku-terbaru" class="buku-section">
  <div class="container">
    <div class="buku-scroll-wrapper">
      <div class="buku-scroll">
        <?php
        $bukuBaru = $conn->query("
            SELECT buku.*, kategori.nama_kategori 
            FROM buku 
            LEFT JOIN kategori ON buku.id_kategori = kategori.id_kategori 
            ORDER BY buku.id_buku DESC 
            LIMIT 6
        ");
        while ($buku = $bukuBaru->fetch_assoc()):
        ?>
          <div class="buku-card">
            <img src="uploads/<?= htmlspecialchars($buku['gambar'] ?: 'default.png') ?>" alt="<?= htmlspecialchars($buku['judul']) ?>">
            <h4><?= htmlspecialchars($buku['judul']) ?></h4>
            <small>Penulis: <?= htmlspecialchars($buku['penulis']) ?></small>
            <small>Kategori: <?= htmlspecialchars($buku['nama_kategori'] ?? '-') ?></small>
          </div>
        <?php endwhile; ?>
        
      </div>
    </div>
  </div>
</section>

<section class="edukasi-section">
  <div class="container">
    <h2>Tips Membaca & Edukasi</h2>
    <div class="edukasi-content">
      <article>
        <h3>Meningkatkan Minat Baca</h3>
        <p>Berikan waktu khusus setiap hari untuk membaca buku favoritmu.</p>
      </article>
      <article>
        <h3>Manfaat Membaca</h3>
        <p>Membaca meningkatkan daya imajinasi dan memperkaya pengetahuan.</p>
      </article>
      <article>
        <h3>Cara Memilih Buku</h3>
        <p>Pilih buku berdasarkan genre yang kamu sukai untuk menjaga semangat membaca.</p>
      </article>
    </div>
  </div>
</section>
  <?php include 'partials/footer.php'; ?>
  <script src="assets/js/scroll.js"></script>
  <script src="assets/js/animasi.js"></script>
  <script src="assets/js/hero-anim.js"></script>
  <script src="https://unpkg.com/scrollreveal"></script>
  <script src="assets/js/scroll-anim.js"></script>
  <script>
  const revealItems = document.querySelectorAll('.card, .kategori-card, .edukasi-content article');

  const revealOnScroll = () => {
    revealItems.forEach(item => {
      const rect = item.getBoundingClientRect();
      const isVisible = rect.top < window.innerHeight - 100;
      if (isVisible) {
        item.style.opacity = 1;
        item.style.transform = 'translateY(0)';
      }
    });
  };

  window.addEventListener('scroll', revealOnScroll);
  window.addEventListener('load', revealOnScroll);
    const edukasiItems = document.querySelectorAll('.edukasi-content article');

  const observer = new IntersectionObserver(entries => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('show');
        observer.unobserve(entry.target);
      }
    });
  }, {
    threshold: 0.2
  });

  edukasiItems.forEach(item => {
    observer.observe(item);
  });
</script>
</body>
</html>
