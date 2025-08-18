<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<header class="main-header">
  <div class="container">
    <!-- Logo -->
    <a href="index.php" class="logo">
      <img src="assets/img/logoTr.png" alt="Logo Litera" class="logo-img" />
      <span class="logo-text">LITERA</span>
    </a>

    <!-- Desktop Navigation -->
    <nav class="nav-menu">
      <a href="index.php" <?php echo ($current_page == 'index.php') ? 'class="active"' : ''; ?>>Beranda</a>
      <a href="kategori.php" <?php echo ($current_page == 'kategori.php') ? 'class="active"' : ''; ?>>Daftar Buku</a>
      <a href="tentang.php" <?php echo ($current_page == 'tentang.php') ? 'class="active"' : ''; ?>>Tentang</a>
      <a href="auth/login.php" class="btn-login">Masuk</a>
    </nav>

    <!-- Mobile Menu Toggle -->
    <button class="mobile-menu-toggle" aria-label="Toggle mobile menu" aria-expanded="false">
      <div class="hamburger">
        <span></span>
        <span></span>
        <span></span>
      </div>
    </button>

    <!-- Mobile Navigation Menu -->
    <nav class="mobile-menu">
      <ul class="mobile-menu-list">
        <li><a href="index.php" <?php echo ($current_page == 'index.php') ? 'class="active"' : ''; ?>> Beranda</a></li>
        <li><a href="kategori.php" <?php echo ($current_page == 'kategori.php') ? 'class="active"' : ''; ?>>Daftar Buku</a></li>
        <li><a href="tentang.php" <?php echo ($current_page == 'tentang.php') ? 'class="active"' : ''; ?>>Tentang</a></li>
        <li><a href="auth/login.php" class="btn-login">Masuk</a></li>
      </ul>
    </nav>
  </div>

  <!-- Mobile Menu Overlay -->
  <div class="mobile-menu-overlay"></div>
</header>
