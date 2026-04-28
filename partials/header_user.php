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

$user_query = mysqli_query($conn, "SELECT * FROM users WHERE id_user = " . intval($user_id));
$user = mysqli_fetch_assoc($user_query);

$kategori = strtolower($user['kategori'] ?? '');
$link_buku = ($kategori === 'pengajar') 
    ? 'daftar_buku_pengajar.php' 
    : 'daftar_buku_umum.php';

$current_page = basename($_SERVER['PHP_SELF']);
?>

<link rel="stylesheet" href="../assets/css/header.css">

<header class="litera-header">

  <!-- TOPBAR -->
  <div class="litera-topbar">
    <span>Perpustakaan Digital</span>
    <div class="divider"></div>
    <span>Senin – Jumat, 08:00 – 20:00</span>
    <div class="divider"></div>
    <span>info@litera.id</span>
  </div>

  <!-- MAIN HEADER -->
  <div class="litera-main">

    <!-- LOGO -->
    <a href="../user/index.php" class="litera-logo">
      <img src="../assets/img/logoTr.png" alt="Logo Litera">
      <div>
        <span class="litera-logo-name">Litera</span>
        <span class="litera-logo-tag">Koleksi &amp; Pengetahuan</span>
      </div>
    </a>

    <!-- NAVIGATION -->
    <nav class="litera-nav">
      <a href="../user/index.php"
         <?= $current_page == 'index.php' ? 'class="active"' : '' ?>>
         Beranda
      </a>

      <a href="<?= $link_buku ?>"
         <?= $current_page == basename($link_buku) ? 'class="active"' : '' ?>>
         Daftar Buku
      </a>

      <a href="../user/riwayat.php"
         <?= $current_page == 'riwayat.php' ? 'class="active"' : '' ?>>
         Data Peminjaman
      </a>

      <a href="../user/profil.php"
         <?= $current_page == 'profil.php' ? 'class="active"' : '' ?>>
         Profil
      </a>

      <a href="../user/tentang_user.php"
         <?= $current_page == 'tentang_user.php' ? 'class="active"' : '' ?>>
         Tentang
      </a>
    </nav>

    <!-- RIGHT SIDE -->
    <div class="litera-actions">

      <!-- USER INFO -->
      <div class="litera-user-info">

        <?php if (!empty($user['foto'])): ?>
          <img 
            src="../assets/img/profil/<?= htmlspecialchars($user['foto']) ?>"
            alt="Foto <?= htmlspecialchars($user['nama']) ?>"
            class="litera-user-avatar">
        <?php else: ?>
          <div class="litera-user-avatar-placeholder">
            <?= strtoupper(substr($user['nama'] ?? 'U', 0, 1)) ?>
          </div>
        <?php endif; ?>

        <span class="litera-user-name">
          <?= htmlspecialchars($user['nama'] ?? 'User') ?>
        </span>
      </div>

      <!-- BUTTON -->
      <a href="../user/logout.php" class="litera-btn-masuk">
        Logout →
      </a>

    </div>

  </div>

</header>