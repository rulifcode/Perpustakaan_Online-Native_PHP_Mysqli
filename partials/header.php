<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>

<link rel="stylesheet" href="assets/css/header.css">

<header class="litera-header" id="litera-header">

  <!-- TOPBAR -->
  <div class="litera-topbar">
    <span>Perpustakaan Digital</span>
    <div class="divider"></div>
    <span>Senin – Jumat, 08:00 – 20:00</span>
    <div class="divider"></div>
    <span>ruliffax@gmail.com</span>
  </div>

  <!-- MAIN BAR -->
  <div class="litera-main">

    <!-- Logo -->
    <a href="index.php" class="litera-logo">
      <img src="assets/img/logoTr.png" alt="Logo Litera" width="36" height="36">
      <div>
        <span class="litera-logo-name">Litera</span>
        <span class="litera-logo-tag">Koleksi &amp; Pengetahuan</span>
      </div>
    </a>

    <!-- Desktop Nav -->
    <nav class="litera-nav">
      <a href="index.php"    <?= $current_page=='index.php'    ? 'class="active"' : '' ?>>Beranda</a>
      <a href="kategori.php" <?= $current_page=='kategori.php' ? 'class="active"' : '' ?>>Daftar Buku</a>
      <a href="tentang.php"  <?= $current_page=='tentang.php'  ? 'class="active"' : '' ?>>Tentang</a>
    </nav>

    <!-- Right -->
    <div class="litera-actions">
      <a href="auth/login.php" class="litera-btn-masuk">Masuk →</a>

      <!-- Hamburger (mobile) -->
      <button class="litera-hamburger" id="litera-hamburger" aria-label="Buka menu">
        <span></span>
        <span></span>
        <span></span>
      </button>
    </div>

  </div>

  <!-- MOBILE FULLSCREEN NAV -->
  <nav class="litera-mobile-nav" id="litera-mobile-nav">
    <button class="litera-mobile-close" id="litera-mobile-close" aria-label="Tutup menu">&#x2715;</button>

    <a href="index.php"    <?= $current_page=='index.php'    ? 'class="active"' : '' ?>>Beranda</a>
    <a href="kategori.php" <?= $current_page=='kategori.php' ? 'class="active"' : '' ?>>Daftar Buku</a>
    <a href="tentang.php"  <?= $current_page=='tentang.php'  ? 'class="active"' : '' ?>>Tentang</a>

    <a href="auth/login.php" class="litera-mobile-btn">Masuk →</a>
  </nav>

</header>

<script>
(function(){
  const header   = document.getElementById('litera-header');
  const burger   = document.getElementById('litera-hamburger');
  const closeBtn = document.getElementById('litera-mobile-close');

  function openNav(){
    header.classList.add('nav-open');
    document.body.style.overflow = 'hidden';
  }

  function closeNav(){
    header.classList.remove('nav-open');
    document.body.style.overflow = '';
  }

  burger.addEventListener('click', function(){
    header.classList.contains('nav-open') ? closeNav() : openNav();
  });

  closeBtn.addEventListener('click', closeNav);

  // close on ESC
  document.addEventListener('keydown', function(e){
    if(e.key === 'Escape') closeNav();
  });

  // close when a mobile nav link is clicked (same page)
  document.querySelectorAll('.litera-mobile-nav a:not(.litera-mobile-btn)').forEach(function(link){
    link.addEventListener('click', closeNav);
  });
})();
</script>