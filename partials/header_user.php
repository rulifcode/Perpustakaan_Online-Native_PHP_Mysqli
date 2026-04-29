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

<header class="litera-header" id="litera-header">

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
      <img src="../assets/img/logoTr.png" alt="Logo Litera" width="36" height="36">
      <div>
        <span class="litera-logo-name">Litera</span>
        <span class="litera-logo-tag">Koleksi &amp; Pengetahuan</span>
      </div>
    </a>

    <!-- DESKTOP NAVIGATION -->
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

      <!-- USER INFO (desktop) -->
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

      <!-- LOGOUT BUTTON -->
      <a href="../user/logout.php" class="litera-btn-masuk">
        Logout →
      </a>

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

    <!-- USER INFO (mobile) -->
    <div class="litera-mobile-user">
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

    <a href="../user/logout.php" class="litera-mobile-btn">Logout →</a>
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

  // Tutup dengan tombol ESC
  document.addEventListener('keydown', function(e){
    if(e.key === 'Escape') closeNav();
  });

  // Tutup saat link nav mobile diklik (selain tombol logout)
  document.querySelectorAll('.litera-mobile-nav a:not(.litera-mobile-btn)').forEach(function(link){
    link.addEventListener('click', closeNav);
  });
})();
</script>