<?php include 'config/config.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image/png" href="assets/img/favicon-32x32.png" />
  <title>Litera — Perpustakaan Digital</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/header.css">
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/footer.css">
  <script src="assets/js/header.js"></script>
</head>
<body>
  <?php include 'partials/header.php'; ?>

  <!-- HERO -->
  <section id="beranda" class="hero">
    <div class="container hero-content">
      <div class="hero-text">
        <span class="hero-eyebrow">Platform Perpustakaan Digital</span>
        <h1>Temukan dunia<br><em>dalam setiap halaman</em></h1>
        <p>Ribuan koleksi buku digital dari berbagai genre, siap menemani perjalanan membacamu kapan saja dan di mana saja.</p>

        <div class="hero-buttons">
          <a href="#buku-terbaru" class="btn-hero-primary">Jelajahi Koleksi</a>
          <a href="auth/login.php" class="btn-hero-secondary">Daftar Sekarang</a>
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
        <img src="assets/img/Buku-cerita-anak.png" alt="Ilustrasi Buku" class="floating">
      </div>
    </div>
  </section>

  <!-- BUKU TERBARU -->
  <section id="buku-terbaru" class="buku-section">
    <div class="container">
      <p class="section-eyebrow">Koleksi Terbaru</p>
      <h2 class="section-title-main">Buku Pilihan Minggu Ini</h2>
    </div>
    <div class="buku-scroll-wrapper">
      <div class="buku-scroll">
        <?php
          $bukuBaru = $conn->query("
            SELECT buku.*, kategori.nama_kategori
            FROM buku
            LEFT JOIN kategori ON buku.id_kategori = kategori.id_kategori
            ORDER BY buku.id_buku DESC
            LIMIT 12
          ");
          $rows = [];
          while ($b = $bukuBaru->fetch_assoc()) $rows[] = $b;
          $allRows = array_merge($rows, $rows);
          foreach ($allRows as $buku):
        ?>
          <div class="buku-card">
            <img src="uploads/<?= htmlspecialchars($buku['gambar'] ?: 'default.png') ?>"
                 alt="<?= htmlspecialchars($buku['judul']) ?>">
            <div class="buku-card-info">
              <h4><?= htmlspecialchars($buku['judul']) ?></h4>
              <p><?= htmlspecialchars($buku['penulis']) ?></p>
              <span class="buku-kategori"><?= htmlspecialchars($buku['nama_kategori'] ?? '-') ?></span>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- EDUKASI -->
  <section class="edukasi-section">
    <div class="container">
      <p class="section-eyebrow">Tips & Edukasi</p>
      <h2 class="section-title-main">Jadikan membaca<br>sebagai gaya hidupmu</h2>
      <div class="edukasi-content">
        <article>
          <h3>Meningkatkan Minat Baca</h3>
          <p>Sisihkan waktu 20 menit setiap hari untuk membaca. Konsistensi kecil membangun kebiasaan besar yang mengubah cara pandangmu.</p>
        </article>
        <article>
          <h3>Manfaat Membaca</h3>
          <p>Membaca memperkuat daya imajinasi, memperluas kosakata, dan terbukti meningkatkan empati serta kemampuan berpikir kritis.</p>
        </article>
        <article>
          <h3>Cara Memilih Buku</h3>
          <p>Mulai dari genre yang kamu sukai, lalu perlahan keluar dari zona nyaman. Setiap buku baru adalah perspektif baru yang menunggu.</p>
        </article>
      </div>
    </div>
  </section>

  <?php include 'partials/footer.php'; ?>

  <script>
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.style.opacity = '1';
          entry.target.style.transform = 'translateY(0)';
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.12 });

    document.querySelectorAll('.edukasi-content article, .hero-stats .stat-item').forEach((el, i) => {
      el.style.opacity = '0';
      el.style.transform = 'translateY(24px)';
      el.style.transition = `opacity 0.6s ease ${i * 0.1}s, transform 0.6s ease ${i * 0.1}s`;
      observer.observe(el);
    });
  </script>
</body>
</html>