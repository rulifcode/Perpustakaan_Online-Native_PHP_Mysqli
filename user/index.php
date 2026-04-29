<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Litera</title>

    <link rel="icon" href="../assets/img/favicon-32x32.png">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="../assets/css/header.css">
    <link rel="stylesheet" href="../assets/css/footer.css">

    <script src="../assets/js/header.js"></script>
</head>
<body>

<?php include '../partials/header_user.php'; ?>

<?php
$foto = (!empty($user['foto_profil']) && file_exists('../assets/img/profil/' . $user['foto_profil']))
    ? '../assets/img/profil/' . $user['foto_profil']
    : '../assets/img/default-avatar.png';
?>

<!-- ================= HERO ================= -->
<section class="hero">
  <div class="container hero-content">

    <div class="hero-text">
      <span class="hero-eyebrow">Dashboard</span>

      <!-- FOTO PROFIL -->
      <div class="hero-profile">
        <img src="<?= $foto ?>" alt="Foto Profil">
      </div>

      <h1>
        Halo, <em><?= htmlspecialchars($user['nama'] ?? $user['username']) ?></em>
      </h1>

      <p>
        Selamat datang kembali di Litera. Kelola aktivitas membaca Anda dan temukan buku terbaik hari ini.
      </p>

      <div class="hero-buttons">
        <a href="daftar_buku.php" class="btn-hero-primary">Jelajahi Buku</a>
        <a href="riwayat.php" class="btn-hero-secondary">Riwayat</a>
      </div>

      <!-- Statistik -->
      <div class="hero-stats">
        <div class="stat-item">
          <span class="stat-number"><?= $total_buku ?? '0' ?></span>
          <span class="stat-label">Koleksi Buku</span>
        </div>
        <div class="stat-item">
          <span class="stat-number"><?= $total_peminjaman ?? '0' ?></span>
          <span class="stat-label">Dipinjam</span>
        </div>
        <div class="stat-item">
          <span class="stat-number"><?= htmlspecialchars($user['kategori']) ?></span>
          <span class="stat-label">Kategori</span>
        </div>
      </div>
    </div>

    <div class="hero-image">
      <img src="../assets/img/tangan.gif" alt="Hero Image" class="floating">
    </div>

  </div>
</section>

<!-- ================= AKTIVITAS ================= -->
<section class="buku-section">
  <div class="container">

    <span class="section-eyebrow">Aktivitas</span>
    <h2 class="section-title-main">Aktivitas Terbaru</h2>

    <div class="buku-scroll-wrapper">
      <div class="buku-scroll">

        <?php if (!empty($riwayat)): ?>
          <?php foreach ($riwayat as $r): ?>
            <div class="buku-card">
              <img src="../assets/img/buku/<?= htmlspecialchars($r['cover']) ?>" alt="Buku">
              <div class="buku-card-info">
                <h4><?= htmlspecialchars($r['judul']) ?></h4>
                <p><?= htmlspecialchars($r['penulis']) ?></p>
                <span class="buku-kategori"><?= htmlspecialchars($r['status']) ?></span>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p>Tidak ada aktivitas terbaru.</p>
        <?php endif; ?>

      </div>
    </div>

  </div>
</section>

<!-- ================= EDUKASI ================= -->
<section class="edukasi-section">
  <div class="container">

    <span class="section-eyebrow">Edukasi</span>
    <h2 class="section-title-main">Tips Membaca & Pengembangan Diri</h2>

    <div class="edukasi-content">

      <article>
        <h3>Meningkatkan Konsistensi</h3>
        <p>Membaca secara rutin setiap hari membantu membangun kebiasaan positif dan meningkatkan fokus.</p>
      </article>

      <article>
        <h3>Pemahaman Lebih Dalam</h3>
        <p>Catat poin penting dari buku untuk membantu memahami isi dan menerapkannya dalam kehidupan.</p>
      </article>

      <article>
        <h3>Eksplorasi Genre</h3>
        <p>Cobalah berbagai genre buku untuk memperluas wawasan dan menemukan minat baru.</p>
      </article>

    </div>

  </div>
</section>

<?php include '../partials/footer.php'; ?>

</body>
</html>