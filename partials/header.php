<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>

<link rel="stylesheet" href="assets/css/header.css">

<header class="litera-header">
  <div class="litera-topbar">
    <span>Perpustakaan Digital</span>
    <div class="divider"></div>
    <span>Senin – Jumat, 08:00 – 20:00</span>
    <div class="divider"></div>
    <span>ruliffax@gmail.com</span>
  </div>

  <div class="litera-main">
    <a href="index.php" class="litera-logo">
      <img src="assets/img/logoTr.png" alt="Logo Litera" width="38" height="38">
      <div>
        <span class="litera-logo-name">Litera</span>
        <span class="litera-logo-tag">Koleksi &amp; Pengetahuan</span>
      </div>
    </a>

    <nav class="litera-nav">
      <a href="index.php" <?= $current_page=='index.php' ? 'class="active"' : '' ?>>Beranda</a>
      <a href="kategori.php" <?= $current_page=='kategori.php' ? 'class="active"' : '' ?>>Daftar Buku</a>
      <a href="tentang.php" <?= $current_page=='tentang.php' ? 'class="active"' : '' ?>>Tentang</a>
    </nav>

    <div class="litera-actions">
      <a href="auth/login.php" class="litera-btn-masuk">Masuk →</a>
    </div>
  </div>
</header>