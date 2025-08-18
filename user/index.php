<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <title>Dashboard - Litera</title>
    <link rel="icon" type="image/png" href="../assets/img/favicon-32x32.png" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="../assets/css/header.css">
  <link rel="stylesheet" href="../assets/css/footer.css">
   <script src="../assets/js/header.js"></script>
    <script src="../assets/js/animasi.js" defer></script>
    <script src="../assets/js/scroll.js" defer></script>
</head>
<body>
<?php include '../partials/header_user.php'; ?>

<!-- Modern Hero Section -->
<section id="beranda" class="hero">
  <div class="container hero-content">
    <div class="hero-text">
      <div class="welcome-badge">
        <span>Selamat datang kembali</span>
      </div>
      <h1>Halo, <span class="gradient-text"><?= htmlspecialchars($user['nama'] ?? $user['username']) ?></span></h1>
      <p class="hero-subtext">Apa yang ingin Anda baca hari ini?</p>
    </div>

    <!-- Enhanced Profile Card with Photo from Database -->
    <div class="hero-profile-card">
      <div class="profile-card">
        <div class="profile-header">
          <div class="profile-avatar-container">
            <?php 
            // Menggunakan path yang sama dengan halaman profil
            $foto_path = $user['foto_profil'] ? "../assets/img/profil/" . $user['foto_profil'] : "../assets/img/default-avatar.png";
            ?>
            
            <?php if (!empty($user['foto_profil']) && file_exists('../assets/img/profil/' . $user['foto_profil'])): ?>
              <img src="<?= htmlspecialchars($foto_path) ?>" 
                   alt="Profile Photo" class="profile-avatar-img">
            <?php else: ?>
              <div class="profile-avatar-default">
                <i class="fas fa-user-circle"></i>
              </div>
            <?php endif; ?>
            
            <!-- Link ke halaman profil untuk edit foto -->
            <div class="profile-edit-overlay" onclick="window.location.href='profil.php'">
              <i class="fas fa-edit"></i>
              <span>Edit Profil</span>
            </div>
          </div>
        </div>
        
        <div class="profile-info">
          <h2><?= htmlspecialchars($user['username'] ?? 'Nama tidak tersedia') ?></h2>
          <div class="profile-detail">
            <i class="fas fa-university"></i>
            <span><?= htmlspecialchars($user['asal_institusi'] ?? '-') ?></span>
          </div>
          <div class="profile-detail">
            <i class="fas fa-id-card"></i>
            <span><?= htmlspecialchars($user['identitas'] ?? '-') ?></span>
          </div>
          <div class="profile-detail">
            <i class="fas fa-tag"></i>
            <span><?= htmlspecialchars(ucfirst($user['kategori'])) ?></span>
          </div>
        </div>
        
        <!-- Edit Profile Button -->
        <div class="profile-actions">
          <a href="profil.php" class="edit-profile-btn">
            <i class="fas fa-user-edit"></i>
            Edit Profil
          </a>
        </div>
      </div>
    </div>
  </div>
  
  <!-- Floating decorative elements -->
  <div class="hero-decor">
    <div class="decor-circle circle-1"></div>
    <div class="decor-circle circle-2"></div>
    <div class="decor-book"></div>
  </div>
</section>


<!-- Modern Edukasi Section -->
<section class="edukasi-section">
  <div class="container">
    <div class="section-header">
      <h2>Tips Membaca & Edukasi</h2>
      <p class="section-subtitle">Tingkatkan pengalaman membacamu dengan tips berikut</p>
    </div>
    
    <div class="edukasi-grid">
      <article class="edukasi-card">
        <div class="card-icon">
          <i class="fas fa-heart"></i>
        </div>
        <h3>Meningkatkan Minat Baca</h3>
        <p>Berikan waktu khusus setiap hari untuk membaca buku favoritmu. Ciptakan ritual membaca yang menyenangkan.</p>
        <a href="#" class="card-link">Pelajari lebih lanjut <i class="fas fa-arrow-right"></i></a>
      </article>
      
      <article class="edukasi-card">
        <div class="card-icon">
          <i class="fas fa-brain"></i>
        </div>
        <h3>Manfaat Membaca</h3>
        <p>Membaca meningkatkan daya imajinasi, memperkaya pengetahuan, dan mengurangi stres secara signifikan.</p>
        <a href="#" class="card-link">Pelajari lebih lanjut <i class="fas fa-arrow-right"></i></a>
      </article>
      
      <article class="edukasi-card">
        <div class="card-icon">
          <i class="fas fa-book-open"></i>
        </div>
        <h3>Cara Memilih Buku</h3>
        <p>Pilih buku berdasarkan genre yang kamu sukai untuk menjaga semangat membaca. Jangan takut mencoba hal baru.</p>
        <a href="#" class="card-link">Pelajari lebih lanjut <i class="fas fa-arrow-right"></i></a>
      </article>
      
      <article class="edukasi-card">
        <div class="card-icon">
          <i class="fas fa-clock"></i>
        </div>
        <h3>Membaca Efektif</h3>
        <p>Gunakan teknik membaca cepat dan aktif untuk menyerap informasi lebih banyak dalam waktu yang lebih singkat.</p>
        <a href="#" class="card-link">Pelajari lebih lanjut <i class="fas fa-arrow-right"></i></a>
      </article>
    </div>
  </div>
</section>

<!-- Footer -->
    <?php include '../partials/footer.php'; ?>

<style>
/* Enhanced CSS for Profile Photo Feature */
.hero {
  position: relative;
  padding: 4rem 0;
  background: linear-gradient(135deg, #f5f7fa 0%, #e4e8f0 100%);
  overflow: hidden;
}

.hero-content {
  display: flex;
  align-items: center;
  justify-content: space-between;
  position: relative;
  z-index: 2;
}

.hero-text {
  flex: 1;
  max-width: 600px;
}

.welcome-badge {
  display: inline-block;
  background: rgba(255, 255, 255, 0.2);
  backdrop-filter: blur(10px);
  padding: 0.5rem 1rem;
  border-radius: 2rem;
  margin-bottom: 1rem;
}

.welcome-badge span {
  font-size: 0.9rem;
  font-weight: 600;
  color: #4a6cf7;
}

.hero h1 {
  font-size: 3rem;
  margin-bottom: 1rem;
  line-height: 1.2;
}

.gradient-text {
  background: linear-gradient(90deg, #4a6cf7 0%, #a855f7 100%);
  -webkit-background-clip: text;
  background-clip: text;
  color: transparent;
}

.hero-subtext {
  font-size: 1.2rem;
  color: #64748b;
  margin-bottom: 2rem;
}

/* Enhanced Profile Card with Photo */
.hero-profile-card {
  margin-left: 2rem;
}

.profile-card {
  background: white;
  border-radius: 1rem;
  padding: 1.5rem;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
  width: 320px;
}

.profile-header {
  display: flex;
  justify-content: center;
  margin-bottom: 1.5rem;
}

.profile-avatar-container {
  position: relative;
  width: 100px;
  height: 100px;
  cursor: pointer;
  transition: all 0.3s ease;
}

.profile-avatar-container:hover .profile-edit-overlay {
  opacity: 1;
}

.profile-avatar-img {
  width: 100%;
  height: 100%;
  border-radius: 50%;
  object-fit: cover;
  border: 4px solid #e2e8f0;
  transition: all 0.3s ease;
}

.profile-avatar-container:hover .profile-avatar-img {
  filter: brightness(0.8);
}

.profile-avatar-default {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  background: #f1f5f9;
  color: #64748b;
  font-size: 3rem;
  border: 4px solid #e2e8f0;
}

.profile-edit-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.7);
  border-radius: 50%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  opacity: 0;
  transition: opacity 0.3s ease;
  color: white;
  font-size: 0.8rem;
}

.profile-edit-overlay i {
  font-size: 1.5rem;
  margin-bottom: 0.25rem;
}

.profile-info h2 {
  font-size: 1.5rem;
  margin-bottom: 1rem;
  text-align: center;
}

.profile-detail {
  display: flex;
  align-items: center;
  margin-bottom: 0.75rem;
  color: #64748b;
}

.profile-detail i {
  margin-right: 0.75rem;
  width: 1.25rem;
  text-align: center;
}

.profile-stats {
  display: flex;
  margin-top: 1.5rem;
  padding-top: 1.5rem;
  border-top: 1px solid #e2e8f0;
}

.stat-item {
  flex: 1;
  text-align: center;
}

.stat-number {
  display: block;
  font-size: 1.5rem;
  font-weight: 700;
  color: #4a6cf7;
}

.stat-label {
  font-size: 0.8rem;
  color: #64748b;
}

.profile-actions {
  margin-top: 1.5rem;
  text-align: center;
}

.edit-profile-btn {
  display: inline-flex;
  align-items: center;
  background: linear-gradient(135deg, #4a6cf7 0%, #a855f7 100%);
  color: white;
  padding: 0.75rem 1.5rem;
  border-radius: 0.5rem;
  text-decoration: none;
  font-weight: 600;
  font-size: 0.9rem;
  transition: all 0.3s ease;
  box-shadow: 0 4px 15px rgba(74, 108, 247, 0.3);
}

.edit-profile-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(74, 108, 247, 0.4);
}

.edit-profile-btn i {
  margin-right: 0.5rem;
}

/* Quick Actions Section */
.quick-actions-section {
  padding: 3rem 0;
  background: white;
}

.quick-actions-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 2rem;
}

.quick-action-card {
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 1rem;
  padding: 2rem;
  text-align: center;
  text-decoration: none;
  color: inherit;
  transition: all 0.3s ease;
}

.quick-action-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
  border-color: #4a6cf7;
}

.action-icon {
  width: 4rem;
  height: 4rem;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 1rem;
  font-size: 1.5rem;
  color: white;
  background: linear-gradient(135deg, #4a6cf7 0%, #a855f7 100%);
}

.quick-action-card h3 {
  font-size: 1.25rem;
  margin-bottom: 0.5rem;
}

.quick-action-card p {
  color: #64748b;
  font-size: 0.9rem;
}

/* Edukasi Section */
.edukasi-section {
  padding: 4rem 0;
  background: #f8fafc;
}

.section-header {
  text-align: center;
  margin-bottom: 3rem;
}

.section-subtitle {
  color: #64748b;
  max-width: 600px;
  margin: 0.5rem auto 0;
}

.edukasi-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 2rem;
}

.edukasi-card {
  background: white;
  border-radius: 1rem;
  padding: 2rem;
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.03);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.edukasi-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}

.card-icon {
  width: 3rem;
  height: 3rem;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 1.5rem;
  font-size: 1.25rem;
  color: white;
  background: linear-gradient(135deg, #4a6cf7 0%, #a855f7 100%);
}

.edukasi-card h3 {
  font-size: 1.25rem;
  margin-bottom: 1rem;
}

.edukasi-card p {
  color: #64748b;
  margin-bottom: 1.5rem;
  line-height: 1.6;
}

.card-link {
  display: inline-flex;
  align-items: center;
  color: #4a6cf7;
  font-weight: 600;
  text-decoration: none;
  font-size: 0.9rem;
}

.card-link i {
  margin-left: 0.5rem;
  transition: transform 0.3s ease;
}

.card-link:hover i {
  transform: translateX(3px);
}

/* Hero Decorations */
.hero-decor {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  pointer-events: none;
}

.decor-circle {
  position: absolute;
  border-radius: 50%;
  background: rgba(74, 108, 247, 0.1);
}

.circle-1 {
  width: 300px;
  height: 300px;
  top: -100px;
  right: -100px;
}

.circle-2 {
  width: 200px;
  height: 200px;
  bottom: -50px;
  left: -50px;
  background: rgba(168, 85, 247, 0.1);
}

@media (max-width: 992px) {
  .hero-content {
    flex-direction: column;
  }
  
  .hero-text {
    margin-bottom: 3rem;
    text-align: center;
  }
  
  .hero-profile-card {
    margin-left: 0;
  }
  
  .hero h1 {
    font-size: 2rem;
  }
}
</style>

<script>
// Enhanced scroll animation
const revealItems = document.querySelectorAll('.hero-text, .profile-card, .edukasi-card, .quick-action-card');

const revealOnScroll = () => {
  revealItems.forEach((item, index) => {
    const rect = item.getBoundingClientRect();
    const isVisible = rect.top < window.innerHeight - 100;
    
    if (isVisible) {
      item.style.transition = `opacity 0.5s ease ${index * 0.1}s, transform 0.5s ease ${index * 0.1}s`;
      item.style.opacity = 1;
      item.style.transform = 'translateY(0)';
    }
  });
};

// Initialize reveal animation
document.addEventListener('DOMContentLoaded', function() {
  // Set initial state for reveal items
  revealItems.forEach(item => {
    item.style.opacity = 0;
    item.style.transform = 'translateY(30px)';
  });
  
  // Trigger initial reveal
  revealOnScroll();
});

window.addEventListener('scroll', revealOnScroll);
window.addEventListener('load', revealOnScroll);
</script>

</body>
</html>