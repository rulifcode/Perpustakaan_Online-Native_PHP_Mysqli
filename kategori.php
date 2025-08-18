
<?php
include 'config/config.php';

$bukuQuery = $conn->query("
    SELECT buku.*, kategori.nama_kategori 
    FROM buku 
    LEFT JOIN kategori ON buku.id_kategori = kategori.id_kategori 
    ORDER BY buku.id_buku DESC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Semua Buku - Litera</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" type="image/png" href="assets/img/favicon-32x32.png" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css"> 
  <link rel="stylesheet" href="assets/css/kategori.css"> 
  <link rel="stylesheet" href="assets/css/header.css">
  <script src="assets/js/header.js"></script>
  <link rel="stylesheet" href="assets/css/footer.css">
  <link rel="stylesheet" href="assets/css/modal-responsive.css">
  <script src="assets/js/animasi.js" defer></script>
  <script src="assets/js/scroll.js" defer></script>
  
  <style>
    /* Carousel Controls Styling */
    .carousel-control-prev,
    .carousel-control-next {
      width: 5%;
      background: rgba(0, 0, 0, 0.2);
      border-radius: 0 10px 10px 0;
      transition: all 0.3s ease;
    }
    
    .carousel-control-next {
      border-radius: 10px 0 0 10px;
    }
    
    .carousel-control-prev:hover,
    .carousel-control-next:hover {
      background: rgba(0, 0, 0, 0.4);
    }
    
    .carousel-control-prev-icon,
    .carousel-control-next-icon {
      width: 30px;
      height: 30px;
      background-size: 20px;
    }
    
    /* Books Section Navigation Controls */
    .books-nav-prev,
    .books-nav-next {
      position: absolute;
      top: 50%;
      transform: translateY(-50%);
      z-index: 10;
      width: 50px;
      height: 50px;
      background: rgba(255, 255, 255, 0.2);
      backdrop-filter: blur(15px);
      border: 1px solid rgba(255, 255, 255, 0.3);
      border-radius: 50%;
      color: #fff;
      font-size: 20px;
      transition: all 0.3s ease;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }
    
    .books-nav-prev {
      left: -25px;
    }
    
    .books-nav-next {
      right: -25px;
    }
    
    .books-nav-prev:hover,
    .books-nav-next:hover {
      background: rgba(255, 255, 255, 0.3);
      border-color: rgba(255, 255, 255, 0.4);
      color: #fff;
      transform: translateY(-50%) scale(1.1);
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
    }
    
    .books-nav-prev:focus,
    .books-nav-next:focus {
      outline: none;
      box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.3);
    }
    
    /* Glassmorphism Books Section Styles */
    #semua-buku {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      position: relative;
      overflow: hidden;
    }
    
    #semua-buku::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="75" cy="75" r="1.5" fill="rgba(255,255,255,0.08)"/><circle cx="50" cy="10" r="0.8" fill="rgba(255,255,255,0.12)"/><circle cx="10" cy="60" r="1.2" fill="rgba(255,255,255,0.09)"/><circle cx="90" cy="40" r="0.6" fill="rgba(255,255,255,0.11)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
      pointer-events: none;
    }
    
    .books-container {
      position: relative;
    }
    
    .glass-card {
      background: rgba(255, 255, 255, 0.15);
      backdrop-filter: blur(20px);
      -webkit-backdrop-filter: blur(20px);
      border: 1px solid rgba(255, 255, 255, 0.2);
      border-radius: 20px;
      transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
      position: relative;
      overflow: hidden;
      box-shadow: 
        0 8px 32px rgba(0, 0, 0, 0.1),
        inset 0 1px 0 rgba(255, 255, 255, 0.2);
    }
    
    .glass-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.15), transparent);
      transition: left 0.6s ease;
      z-index: 1;
    }
    
    .glass-card:hover::before {
      left: 100%;
    }
    
    .glass-card:hover {
      transform: translateY(-12px) scale(1.02);
      background: rgba(255, 255, 255, 0.25);
      border: 1px solid rgba(255, 255, 255, 0.3);
      box-shadow: 
        0 20px 40px rgba(0, 0, 0, 0.15),
        0 0 0 1px rgba(255, 255, 255, 0.1),
        inset 0 1px 0 rgba(255, 255, 255, 0.3);
    }
    
    .glass-card .card-img-top {
      border-radius: 16px 16px 0 0;
      height: 280px;
      object-fit: cover;
      transition: all 0.4s ease;
      position: relative;
      z-index: 2;
    }
    
    .glass-card:hover .card-img-top {
      transform: scale(1.05);
      filter: brightness(1.1) contrast(1.1);
    }
    
    .glass-card .card-body {
      background: rgba(255, 255, 255, 0.1);
      backdrop-filter: blur(10px);
      position: relative;
      z-index: 2;
      border-radius: 0;
      padding: 1.5rem;
    }
    
    .glass-card .card-title {
      color: #fff;
      font-weight: 600;
      font-size: 1.1rem;
      margin-bottom: 0.5rem;
      text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
      line-height: 1.3;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }
    
    .glass-card .card-text {
      color: rgba(255, 255, 255, 0.85);
      font-size: 0.9rem;
      text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
      margin-bottom: 0;
      display: -webkit-box;
      -webkit-line-clamp: 1;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }
    
    .glass-card .card-footer {
      background: rgba(255, 255, 255, 0.08);
      backdrop-filter: blur(15px);
      border: none;
      border-radius: 0 0 16px 16px;
      padding: 1.2rem;
      position: relative;
      z-index: 2;
    }
    
    .glass-btn {
      background: rgba(255, 255, 255, 0.2);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.3);
      color: #fff;
      padding: 8px 24px;
      border-radius: 25px;
      font-weight: 500;
      font-size: 0.9rem;
      transition: all 0.3s ease;
      text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
      position: relative;
      overflow: hidden;
    }
    
    .glass-btn::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
      transition: left 0.5s ease;
    }
    
    .glass-btn:hover::before {
      left: 100%;
    }
    
    .glass-btn:hover {
      background: rgba(255, 255, 255, 0.3);
      border-color: rgba(255, 255, 255, 0.4);
      color: #fff;
      transform: translateY(-2px);
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
    }
    
    .glass-btn:active {
      transform: translateY(0);
    }
    
    .section-title {
      color: #fff;
      font-size: 2.5rem;
      font-weight: 700;
      text-align: center;
      margin-bottom: 3rem;
      text-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
      position: relative;
    }
    
    .section-title::after {
      content: '';
      position: absolute;
      bottom: -15px;
      left: 50%;
      transform: translateX(-50%);
      width: 80px;
      height: 4px;
      background: linear-gradient(90deg, rgba(255, 255, 255, 0.6), rgba(255, 255, 255, 0.2));
      border-radius: 2px;
    }
    
    /* Books carousel functionality */
    .books-carousel {
      overflow: hidden;
      position: relative;
    }
    
    .books-track {
      display: flex;
      transition: transform 0.5s ease;
      gap: 1.5rem;
    }
    
    .book-slide {
      flex: 0 0 auto;
      width: calc(25% - 1.125rem);
    }
    
    /* Animation for cards on scroll */
    .glass-card {
      opacity: 0;
      transform: translateY(30px);
      animation: fadeInUp 0.6s ease forwards;
    }
    
    .glass-card:nth-child(1) { animation-delay: 0.1s; }
    .glass-card:nth-child(2) { animation-delay: 0.2s; }
    .glass-card:nth-child(3) { animation-delay: 0.3s; }
    .glass-card:nth-child(4) { animation-delay: 0.4s; }
    .glass-card:nth-child(5) { animation-delay: 0.5s; }
    .glass-card:nth-child(6) { animation-delay: 0.6s; }
    .glass-card:nth-child(7) { animation-delay: 0.7s; }
    .glass-card:nth-child(8) { animation-delay: 0.8s; }
    
    @keyframes fadeInUp {
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    
    /* Responsive adjustments */
    @media (max-width: 1200px) {
      .book-slide {
        width: calc(33.333% - 1rem);
      }
    }
    
    @media (max-width: 768px) {
      .glass-card .card-img-top {
        height: 220px;
      }
      
      .section-title {
        font-size: 2rem;
        margin-bottom: 2rem;
      }
      
      .glass-card:hover {
        transform: translateY(-8px) scale(1.01);
      }
      
      .book-slide {
        width: calc(50% - 0.75rem);
      }
      
      .books-nav-prev,
      .books-nav-next {
        width: 40px;
        height: 40px;
        font-size: 16px;
      }
      
      .books-nav-prev {
        left: -20px;
      }
      
      .books-nav-next {
        right: -20px;
      }
    }
    
    @media (max-width: 576px) {
      .glass-card .card-img-top {
        height: 200px;
      }
      
      .glass-card .card-body {
        padding: 1rem;
      }
      
      .glass-card .card-footer {
        padding: 1rem;
      }
      
      .book-slide {
        width: calc(100% - 0.5rem);
      }
      
      .books-nav-prev,
      .books-nav-next {
        display: none;
      }
    }
  </style>
</head>
<body>

<!-- Include your existing header here -->
<?php include 'partials/header.php'; ?>

<!-- Hero Carousel Section -->
<section id="carouselBuku" class="mb-0">
  <div id="literaCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="4000">
    <div class="carousel-inner">
      <div class="carousel-item active">
        <img src="assets/img/baca2.png" class="d-block w-100" alt="Slide 1">
        <div class="carousel-caption d-none d-md-block">
          <h3 class="fw-bold">Temukan Buku Favoritmu</h3>
          <p>Jelajahi berbagai kategori bacaan terbaik.</p>
        </div>
      </div>
      <div class="carousel-item">
        <img src="assets/img/Siswa-perempuan-sedang-memegang-buku-di-antara-dua-orang-teman-lelaki-dan-wanita-di-perpustakaan_l3rWA.jpg" class="d-block w-100" alt="Slide 2">
        <div class="carousel-caption d-none d-md-block">
          <h3 class="fw-bold">Koleksi Terbaru</h3>
          <p>Update setiap minggu dengan buku-buku baru.</p>
        </div>
      </div>
      <div class="carousel-item">
        <img src="assets/img/baca4.jpg" class="d-block w-100" alt="Slide 3">
        <div class="carousel-caption d-none d-md-block">
          <h3 class="fw-bold">Baca Dimana Saja</h3>
          <p>Perpustakaan digital yang mudah diakses.</p>
        </div>
      </div>
    </div>
    <!-- Carousel Controls -->
    <button class="carousel-control-prev" type="button" data-bs-target="#literaCarousel" data-bs-slide="prev">
      <span class="carousel-control-prev-icon" aria-hidden="true"></span>
      <span class="visually-hidden">Previous</span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#literaCarousel" data-bs-slide="next">
      <span class="carousel-control-next-icon" aria-hidden="true"></span>
      <span class="visually-hidden">Next</span>
    </button>
  </div>
</section>

<!-- Glassmorphism All Books Section -->
<section id="semua-buku" class="py-5">
  <div class="container">
    <h2 class="section-title">Daftar Buku Terbaru</h2>

    <div class="books-container">
      <!-- Navigation Controls -->
      <button class="books-nav-prev" onclick="slideBooks('prev')" aria-label="Previous books">
        &#8249;
      </button>
      <button class="books-nav-next" onclick="slideBooks('next')" aria-label="Next books">
        &#8250;
      </button>
      
      <!-- Books Carousel -->
      <div class="books-carousel">
        <div class="books-track" id="booksTrack">
          <?php while ($buku = $bukuQuery->fetch_assoc()): ?>
            <div class="book-slide">
              <div class="card glass-card h-100">
                <img src="uploads/<?= htmlspecialchars($buku['gambar'] ?: 'default.png') ?>" class="card-img-top" alt="<?= htmlspecialchars($buku['judul']) ?>">
                <div class="card-body">
                  <h5 class="card-title"><?= htmlspecialchars($buku['judul']) ?></h5>
                  <p class="card-text"><?= htmlspecialchars($buku['penulis']) ?></p>
                </div>
                <div class="card-footer text-center">
                  <button type="button" class="btn glass-btn" data-bs-toggle="modal" data-bs-target="#bukuModal" 
                          onclick="showBookDetail(
                            '<?= htmlspecialchars($buku['id_buku']) ?>',
                            '<?= htmlspecialchars($buku['judul']) ?>',
                            '<?= htmlspecialchars($buku['penulis']) ?>',
                            '<?= htmlspecialchars($buku['penerbit'] ?? '-') ?>',
                            '<?= htmlspecialchars($buku['tahun_terbit'] ?? '-') ?>',
                            '<?= htmlspecialchars($buku['nama_kategori'] ?? '-') ?>',
                            '<?= htmlspecialchars($buku['gambar'] ?: 'default.png') ?>',
                            '<?= htmlspecialchars($buku['stok'] ?? '0') ?>'
                          )">
                    Detail
                  </button>
                </div>
              </div>
            </div>
          <?php endwhile; ?>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Book Details Modal -->
<div class="modal fade" id="bukuModal" tabindex="-1" aria-labelledby="bukuModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content modern-modal">
      <button type="button" class="btn-close-modern" data-bs-dismiss="modal" aria-label="Close">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </button>
      
      <div class="modal-body p-0">
        <div class="row g-0 h-100">
          <!-- Image Section -->
          <div class="col-lg-5 book-cover-section">
            <div class="book-cover-container">
              <div class="book-cover-wrapper">
                <img id="modalBookImage" src="" class="book-cover-image" alt="Cover Buku">
                <div class="book-cover-overlay"></div>
              </div>
              <div class="floating-stock-badge" id="floatingStockBadge">
                <span id="stockIcon"></span>
                <span id="stockText"></span>
              </div>
            </div>
          </div>
          
          <!-- Content Section -->
          <div class="col-lg-7 book-details-section">
            <div class="book-details-content">
              <div class="book-category-tag" id="modalBookCategoryTag"></div>
              
              <h1 class="book-title" id="modalBookTitle"></h1>
              <p class="book-author" id="modalBookAuthor"></p>
              
              <div class="book-meta-grid">
                <div class="meta-item">
                  <div class="meta-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                      <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                  </div>
                  <div class="meta-content">
                    <span class="meta-label">Penerbit</span>
                    <span class="meta-value" id="modalBookPublisher"></span>
                  </div>
                </div>
                
                <div class="meta-item">
                  <div class="meta-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <rect x="3" y="4" width="18" height="18" rx="2" ry="2" stroke="currentColor" stroke-width="2" fill="none"/>
                      <line x1="16" y1="2" x2="16" y2="6" stroke="currentColor" stroke-width="2"/>
                      <line x1="8" y1="2" x2="8" y2="6" stroke="currentColor" stroke-width="2"/>
                      <line x1="3" y1="10" x2="21" y2="10" stroke="currentColor" stroke-width="2"/>
                    </svg>
                  </div>
                  <div class="meta-content">
                    <span class="meta-label">Tahun Terbit</span>
                    <span class="meta-value" id="modalBookYear"></span>
                  </div>
                </div>
                
                <div class="meta-item">
                  <div class="meta-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z" stroke="currentColor" stroke-width="2" fill="none"/>
                      <line x1="7" y1="7" x2="7.01" y2="7" stroke="currentColor" stroke-width="2"/>
                    </svg>
                  </div>
                  <div class="meta-content">
                    <span class="meta-label">ID Buku</span>
                    <span class="meta-value" id="modalBookId"></span>
                  </div>
                </div>
                
                <div class="meta-item">
                  <div class="meta-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z" stroke="currentColor" stroke-width="2" fill="none"/>
                      <polyline points="3.27,6.96 12,12.01 20.73,6.96" stroke="currentColor" stroke-width="2"/>
                      <line x1="12" y1="22.08" x2="12" y2="12" stroke="currentColor" stroke-width="2"/>
                    </svg>
                  </div>
                  <div class="meta-content">
                    <span class="meta-label">Stok Tersedia</span>
                    <span class="meta-value" id="modalBookStock"></span>
                  </div>
                </div>
              </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modern Modal Styles with Mobile Responsive -->
<style>
/* Base Styles */
.modern-modal {
  border: none;
  border-radius: 16px;
  box-shadow: 0 32px 64px rgba(0, 0, 0, 0.12);
  overflow: hidden;
  position: relative;
  animation: modalSlideUp 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes modalSlideUp {
  from { opacity: 0; transform: translateY(60px) scale(0.9); }
  to { opacity: 1; transform: translateY(0) scale(1); }
}

.btn-close-modern {
  position: absolute;
  top: 15px;
  right: 15px;
  z-index: 1000;
  background: rgba(255, 255, 255, 0.9);
  border: none;
  border-radius: 12px;
  width: 36px;
  height: 36px;
  padding: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
  backdrop-filter: blur(10px);
}

.btn-close-modern:hover {
  background: rgba(255, 255, 255, 1);
  transform: scale(1.1);
}

/* Book Cover Section */
.book-cover-section {
  background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
  position: relative;
  min-height: 400px;
  overflow: hidden;
}

.book-cover-container {
  position: relative;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 30px;
}

.book-cover-wrapper {
  position: relative;
  max-width: 240px;
  transform: perspective(1000px) rotateY(-10deg) rotateX(5deg);
  transition: all 0.5s ease;
}

.book-cover-wrapper:hover {
  transform: perspective(1000px) rotateY(0deg) rotateX(0deg) scale(1.05);
}

.book-cover-image {
  width: 100%;
  height: auto;
  border-radius: 12px;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
  transition: all 0.3s ease;
}

.floating-stock-badge {
  position: absolute;
  top: -8px;
  right: -8px;
  background: linear-gradient(135deg, #667eea 0%,rgb(18, 171, 231) 100%);
  color: white;
  padding: 8px 14px;
  border-radius: 16px;
  display: flex;
  align-items: center;
  gap: 6px;
  font-weight: 600;
  font-size: 13px;
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.05); }
}

/* Book Details Section */
.book-details-section {
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
  position: relative;
}

.book-details-content {
  padding: 30px;
  height: 100%;
  display: flex;
  flex-direction: column;
  justify-content: center;
}

.book-category-tag {
  display: inline-block;
  background: linear-gradient(135deg, #ff6b6b, #ee5a24);
  color: white;
  padding: 6px 12px;
  border-radius: 16px;
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 1px;
  margin-bottom: 15px;
  box-shadow: 0 4px 12px rgba(255, 107, 107, 0.3);
}

.book-title {
  font-size: 1.8rem;
  font-weight: 700;
  color: #2c3e50;
  margin-bottom: 10px;
  line-height: 1.3;
}

.book-author {
  font-size: 1.1rem;
  color: #7f8c8d;
  margin-bottom: 25px;
  font-style: italic;
}

.book-meta-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 15px;
  margin-bottom: 30px;
}

.meta-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 15px;
  background: rgba(255, 255, 255, 0.8);
  border-radius: 12px;
  border: 1px solid rgba(255, 255, 255, 0.2);
  transition: all 0.3s ease;
  backdrop-filter: blur(10px);
}

.meta-item:hover {
  background: rgba(255, 255, 255, 0.95);
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
}

.meta-icon {
  flex-shrink: 0;
  width: 32px;
  height: 32px;
  background: linear-gradient(135deg, #667eea 0%,rgb(0, 208, 255) 100%);
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
}

.meta-icon svg {
  width: 18px;
  height: 18px;
}

.meta-content {
  display: flex;
  flex-direction: column;
  min-width: 0;
}

.meta-label {
  font-size: 11px;
  color: #7f8c8d;
  font-weight: 600;
  margin-bottom: 2px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.meta-value {
  font-size: 14px;
  color: #2c3e50;
  font-weight: 600;
  word-break: break-word;
}

.action-buttons {
  display: flex;
  gap: 12px;
}

.btn-modern {
  position: relative;
  border: none;
  border-radius: 12px;
  padding: 12px 20px;
  font-weight: 600;
  font-size: 14px;
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
  transition: all 0.3s ease;
  min-width: 140px;
  justify-content: center;
  overflow: hidden;
}

.btn-modern:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none !important;
}

.btn-modern.btn-primary {
  background: linear-gradient(135deg, #667eea 0%,rgb(29, 183, 226) 100%);
  color: white;
  box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
}

.btn-modern.btn-primary:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(102, 126, 234, 0.6);
}

.btn-modern.btn-secondary {
  background: rgba(255, 255, 255, 0.9);
  color: #2c3e50;
  border: 2px solid rgba(255, 255, 255, 0.3);
}

.btn-modern.btn-secondary:hover {
  background: rgba(255, 255, 255, 1);
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
}

.btn-ripple {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: radial-gradient(circle, rgba(255, 255, 255, 0.3) 0%, transparent 70%);
  transform: scale(0);
  border-radius: inherit;
  pointer-events: none;
}

@keyframes ripple {
  0% {
    transform: scale(0);
    opacity: 1;
  }
  100% {
    transform: scale(4);
    opacity: 0;
  }
}

/* Carousel Responsive */
#carouselBuku .carousel-item img {
  height: 50vh;
  object-fit: cover;
  filter: brightness(70%);
}

.carousel-caption h3 {
  font-size: 1.5rem;
}

.carousel-caption p {
  font-size: 1rem;
}

/* Book Cards Responsive */
#semua-buku .card {
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

#semua-buku .card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

#semua-buku .card-img-top {
  height: 200px;
  object-fit: cover;
  transition: transform 0.3s ease;
}

#semua-buku .card:hover .card-img-top {
  transform: scale(1.05);
}

#semua-buku .card-title {
  font-size: 1rem;
  margin-bottom: 0.5rem;
  font-weight: 600;
}

#semua-buku .card-text {
  font-size: 0.85rem;
}

/* Responsive Breakpoints */
@media (max-width: 1199.98px) {
  .book-title {
    font-size: 1.6rem;
  }
  
  .book-meta-grid {
    grid-template-columns: 1fr;
    gap: 12px;
  }
  
  .meta-item {
    padding: 12px;
  }
  
  .action-buttons {
    flex-direction: column;
  }
  
  .btn-modern {
    width: 100%;
    min-width: auto;
  }
}

@media (max-width: 991.98px) {
  .modal-dialog {
    max-width: 90%;
    margin: 1rem auto;
  }
  
  .book-cover-section {
    min-height: 350px;
  }
  
  .book-cover-wrapper {
    max-width: 200px;
    transform: none;
  }
  
  .book-title {
    font-size: 1.5rem;
  }
  
  .book-author {
    font-size: 1rem;
  }
  
  .book-details-content {
    padding: 25px;
  }
}

@media (max-width: 767.98px) {
  .modal-dialog {
    margin: 0.5rem;
    max-width: none;
  }
  
  .modern-modal {
    border-radius: 12px;
    max-height: 95vh;
    overflow-y: auto;
  }
  
  .modal-body {
    overflow-y: auto;
  }
  
  .row.g-0 {
    flex-direction: column;
  }
  
  .book-cover-section {
    min-height: 280px;
    order: 1;
  }
  
  .book-details-section {
    order: 2;
  }
  
  .book-cover-container {
    padding: 20px;
  }
  
  .book-cover-wrapper {
    max-width: 160px;
  }
  
  .book-details-content {
    padding: 20px;
  }
  
  .book-title {
    font-size: 1.4rem;
    margin-bottom: 8px;
  }
  
  .book-author {
    font-size: 0.95rem;
    margin-bottom: 20px;
  }
  
  .book-meta-grid {
    margin-bottom: 25px;
  }
  
  .meta-item {
    padding: 10px;
    gap: 10px;
  }
  
  .meta-icon {
    width: 28px;
    height: 28px;
  }
  
  .meta-icon svg {
    width: 16px;
    height: 16px;
  }
  
  .meta-value {
    font-size: 13px;
  }
  
  .btn-modern {
    padding: 10px 15px;
    font-size: 13px;
  }
  
  .btn-close-modern {
    top: 10px;
    right: 10px;
    width: 32px;
    height: 32px;
  }
  
  .floating-stock-badge {
    padding: 6px 10px;
    font-size: 11px;
    top: -6px;
    right: -6px;
  }
  
  #carouselBuku .carousel-item img {
    height: 40vh;
  }
  
  .carousel-caption h3 {
    font-size: 1.3rem;
  }
  
  .carousel-caption p {
    font-size: 0.9rem;
  }
}

@media (max-width: 575.98px) {
  .modal-dialog {
    margin: 0.25rem;
  }
  
  .book-cover-section {
    min-height: 250px;
  }
  
  .book-cover-wrapper {
    max-width: 140px;
  }
  
  .book-title {
    font-size: 1.3rem;
  }
  
  .book-author {
    font-size: 0.9rem;
    margin-bottom: 18px;
  }
  
  .book-details-content {
    padding: 15px;
  }
  
  .book-meta-grid {
    gap: 10px;
    margin-bottom: 20px;
  }
  
  .meta-item {
    padding: 8px;
    gap: 8px;
  }
  
  .meta-icon {
    width: 24px;
    height: 24px;
  }
  
  .meta-icon svg {
    width: 14px;
    height: 14px;
  }
  
  .meta-value {
    font-size: 12px;
  }
  
  .btn-modern {
    padding: 8px 12px;
    font-size: 12px;
    gap: 6px;
  }
  
  .action-buttons {
    gap: 8px;
  }
  
  #carouselBuku .carousel-item img {
    height: 35vh;
  }
  
  #semua-buku .card-img-top {
    height: 180px;
  }
}

/* Dark Mode Support */
@media (prefers-color-scheme: dark) {
  .book-details-section {
    background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
  }
  
  .book-title {
    color: #ecf0f1;
  }
  
  .book-author {
    color: #bdc3c7;
  }
  
  .meta-item {
    background: rgba(52, 73, 94, 0.8);
    border-color: rgba(255, 255, 255, 0.1);
  }
  
  .meta-item:hover {
    background: rgba(52, 73, 94, 0.95);
  }
  
  .meta-value {
    color: #ecf0f1;
  }
  
  .meta-label {
    color: #95a5a6;
  }
  
  .btn-modern.btn-secondary {
    background: rgba(44, 62, 80, 0.8);
    color: #ecf0f1;
    border-color: rgba(255, 255, 255, 0.2);
  }
  
  .btn-modern.btn-secondary:hover {
    background: rgba(44, 62, 80, 1);
  }
}

/* Loading states */
.btn-modern.loading {
  pointer-events: none;
}

.btn-modern.loading .btn-text {
  opacity: 0.7;
}

.btn-modern.loading .btn-icon {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}
</style>

<?php include 'partials/footer.php'; ?>

<!-- JavaScript Libraries -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/scrollreveal"></script>

<!-- Custom Scripts -->
<script>
  let currentPosition = 0;
const track = document.getElementById('booksTrack');
const slides = document.querySelectorAll('.book-slide');
const totalSlides = slides.length;
const slidesPerView = getSlidesPerView();
const maxPosition = Math.max(0, totalSlides - slidesPerView);

function getSlidesPerView() {
  if (window.innerWidth <= 576) return 1;
  if (window.innerWidth <= 768) return 2;
  if (window.innerWidth <= 1200) return 3;
  return 4;
}

function slideBooks(direction) {
  if (direction === 'next' && currentPosition < maxPosition) {
    currentPosition++;
  } else if (direction === 'prev' && currentPosition > 0) {
    currentPosition--;
  }
  
  const slideWidth = slides[0].offsetWidth + 24; // width + gap
  const translateX = -currentPosition * slideWidth;
  track.style.transform = `translateX(${translateX}px)`;
  
  updateNavigationButtons();
}

function updateNavigationButtons() {
  const prevBtn = document.querySelector('.books-nav-prev');
  const nextBtn = document.querySelector('.books-nav-next');
  
  prevBtn.style.opacity = currentPosition === 0 ? '0.5' : '1';
  nextBtn.style.opacity = currentPosition === maxPosition ? '0.5' : '1';
  
  prevBtn.style.pointerEvents = currentPosition === 0 ? 'none' : 'auto';
  nextBtn.style.pointerEvents = currentPosition === maxPosition ? 'none' : 'auto';
}

// Handle window resize
window.addEventListener('resize', () => {
  const newSlidesPerView = getSlidesPerView();
  const newMaxPosition = Math.max(0, totalSlides - newSlidesPerView);
  
  if (currentPosition > newMaxPosition) {
    currentPosition = newMaxPosition;
  }
  
  slideBooks('');
});

// Initialize navigation buttons
updateNavigationButtons();

// Auto-scroll functionality (optional)
setInterval(() => {
  if (currentPosition < maxPosition) {
    slideBooks('next');
  } else {
    currentPosition = 0;
    slideBooks('');
  }
}, 5000);
  function showBookDetail(id, judul, penulis, penerbit, tahun, kategori, gambar, stok) {
    // Your existing modal function code here
    console.log('Book details:', {id, judul, penulis, penerbit, tahun, kategori, gambar, stok});
}

// Enhanced hover effects
document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.glass-card');
    
    cards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.zIndex = '10';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.zIndex = '1';
        });
    });
});
// Function to show book detail in modal
function showBookDetail(id, judul, penulis, penerbit, tahun, kategori, gambar, stok) {
    // Populate modal data
    document.getElementById('modalBookId').textContent = id;
    document.getElementById('modalBookTitle').textContent = judul;
    document.getElementById('modalBookAuthor').textContent = `oleh ${penulis}`;
    document.getElementById('modalBookPublisher').textContent = penerbit || 'Tidak tersedia';
    document.getElementById('modalBookYear').textContent = tahun || 'Tidak tersedia';
    document.getElementById('modalBookCategoryTag').textContent = kategori || 'Umum';
    document.getElementById('modalBookStock').textContent = `${stok} eksemplar`;
    
    // Set book cover image
    const modalImage = document.getElementById('modalBookImage');
    modalImage.src = 'uploads/' + gambar;
    modalImage.alt = 'Cover ' + judul;
    
    // Update floating stock badge
    const floatingBadge = document.getElementById('floatingStockBadge');
    const stockIcon = document.getElementById('stockIcon');
    const stockText = document.getElementById('stockText');
    const pinjamBtn = document.getElementById('pinjamBtn');
    
    if (parseInt(stok) > 0) {
        stockIcon.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>';
        stockText.textContent = 'Tersedia';
        floatingBadge.style.background = 'linear-gradient(135deg, #00b894 0%, #00cec9 100%)';
        
        pinjamBtn.disabled = false;
        pinjamBtn.querySelector('.btn-text').textContent = 'Pinjam Sekarang';
        pinjamBtn.querySelector('.btn-icon').innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>';
    } else {
        stockIcon.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"></line></svg>';
        stockText.textContent = 'Habis';
        floatingBadge.style.background = 'linear-gradient(135deg, #e17055 0%, #d63031 100%)';
        
        pinjamBtn.disabled = true;
        pinjamBtn.querySelector('.btn-text').textContent = 'Stok Habis';
        pinjamBtn.querySelector('.btn-icon').innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>';
    }
    
    // Set pinjam button action
    pinjamBtn.onclick = function(e) {
        if (parseInt(stok) > 0) {
            // Add ripple effect
            const ripple = this.querySelector('.btn-ripple');
            ripple.style.transform = 'scale(0)';
            ripple.style.opacity = '1';
            
            setTimeout(() => {
                ripple.style.animation = 'ripple 0.6s ease-out';
            }, 10);
            
            // Success feedback
            setTimeout(() => {
                const originalText = this.querySelector('.btn-text').textContent;
                const originalIcon = this.querySelector('.btn-icon').innerHTML;
                
                this.querySelector('.btn-text').textContent = 'Berhasil!';
                this.querySelector('.btn-icon').innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>';
                this.style.background = 'linear-gradient(135deg, #00b894 0%, #00cec9 100%)';
                
                setTimeout(() => {
                    this.querySelector('.btn-text').textContent = originalText;
                    this.querySelector('.btn-icon').innerHTML = originalIcon;
                    this.style.background = 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
                }, 2000);
            }, 300);
        }
    };
}

// Add button click animations
document.addEventListener('DOMContentLoaded', function() {
    const buttons = document.querySelectorAll('.btn-modern');
    
    buttons.forEach(button => {
        button.addEventListener('click', function(e) {
            const ripple = this.querySelector('.btn-ripple');
            if (ripple && !this.disabled) {
                ripple.style.transform = 'scale(0)';
                ripple.style.opacity = '1';
                
                setTimeout(() => {
                    ripple.style.animation = 'ripple 0.6s ease-out';
                }, 10);
            }
        });
    });
    
    // Initialize ScrollReveal
    ScrollReveal().reveal('.card', {
        delay: 200,
        distance: '20px',
        origin: 'bottom',
        interval: 100,
        easing: 'cubic-bezier(0.5, 0, 0, 1)',
        reset: true
    });
});

// Responsive carousel height
function adjustCarouselHeight() {
    const carousel = document.querySelector('#carouselBuku .carousel-item img');
    if (carousel) {
        const viewportHeight = window.innerHeight;
        carousel.style.height = Math.min(viewportHeight * 0.5, 500) + 'px';
    }
}

window.addEventListener('load', adjustCarouselHeight);
window.addEventListener('resize', adjustCarouselHeight);
</script>
</body>
</html>