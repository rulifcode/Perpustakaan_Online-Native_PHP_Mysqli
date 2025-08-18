<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

include '../config/config.php';

$user_id = $_SESSION['user_id'];
$user_query = mysqli_query($conn, "SELECT * FROM users WHERE id_user = '$user_id'");
$user = mysqli_fetch_assoc($user_query);

$kategori = strtolower($user['kategori'] ?? '');
$link_buku = ($kategori === 'pengajar') ? 'daftar_buku_pengajar.php' : 'daftar_buku_umum.php';

// Ambil halaman saat ini
$current_page = basename($_SERVER['PHP_SELF']);
?>

<header class="main-header">
  <div class="container">
    <!-- Logo -->
    <a href="../user/index.php" class="logo">
      <img src="../assets/img/logoTr.png" alt="Logo Litera" class="logo-img" />
      <span class="logo-text">LITERA</span>
    </a>

    <!-- Desktop Navigation -->
    <nav class="nav-menu">
      <a href="../user/index.php" <?= ($current_page == 'index.php') ? 'class="active"' : ''; ?>>Beranda</a>
      <a href="<?= $link_buku ?>" <?= ($current_page == $link_buku) ? 'class="active"' : ''; ?>>Daftar Buku</a>
      <a href="../user/riwayat.php" <?= ($current_page == 'riwayat.php') ? 'class="active"' : ''; ?>>Data Peminjaman</a>
      <a href="../user/profil.php" <?= ($current_page == 'profil.php') ? 'class="active"' : ''; ?>>Profil</a>
      <a href="../user/tentang_user.php" <?= ($current_page == 'tentang_user.php') ? 'class="active"' : ''; ?>>Tentang</a>
      <a href="../user/logout.php" class="btn-logout">Logout</a>
    </nav>

    <!-- Mobile Menu Toggle -->
    <button class="mobile-menu-toggle" aria-label="Toggle mobile menu" aria-expanded="false">
      <div class="hamburger">
        <span></span>
        <span></span>
        <span></span>
      </div>
    </button>

    <!-- Mobile Navigation -->
    <nav class="mobile-menu">
      <ul class="mobile-menu-list">
        <li><a href="../user/index.php" <?= ($current_page == 'index.php') ? 'class="active"' : ''; ?>>Beranda</a></li>
        <li><a href="<?= $link_buku ?>" <?= ($current_page == $link_buku) ? 'class="active"' : ''; ?>>Daftar Buku</a></li>
        <li><a href="../user/riwayat.php" <?= ($current_page == 'riwayat.php') ? 'class="active"' : ''; ?>>Data Peminjaman</a></li>
        <li><a href="../user/profil.php" <?= ($current_page == 'profil.php') ? 'class="active"' : ''; ?>>Profil</a></li>
        <li><a href="../user/tentang_user.php" <?= ($current_page == 'tentang_user.php') ? 'class="active"' : ''; ?>>Tentang</a></li>
        <li><a href="../user/logout.php" class="btn-logout">Logout</a></li>
      </ul>
    </nav>
  </div>

  <!-- Overlay -->
  <div class="mobile-menu-overlay"></div>
</header>