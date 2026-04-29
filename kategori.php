<?php
include 'config/config.php';

$bukuQuery = $conn->query("
    SELECT buku.*, kategori.nama_kategori 
    FROM buku 
    LEFT JOIN kategori ON buku.id_kategori = kategori.id_kategori 
    ORDER BY buku.id_buku DESC
");
$rows = [];
while ($b = $bukuQuery->fetch_assoc()) $rows[] = $b;
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image/png" href="assets/img/favicon-32x32.png" />
  <title>Semua Buku — Litera</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/header.css">
  <link rel="stylesheet" href="assets/css/footer.css">
  <script src="assets/js/header.js"></script>
  <style>
    :root {
      --navy: #0d1f4e;
      --navy-mid: #1f3c88;
      --gold: #c9a84c;
      --gold-light: #e6c878;
      --cream: #f7f4ef;
      --cream-dark: #ede9e0;
      --white: #ffffff;
      --text-body: #3a3a3a;
      --text-muted: #7a7a7a;
    }

    * { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: 'DM Sans', sans-serif;
      background: var(--cream);
      color: var(--text-body);
    }

    .container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 40px;
    }

    /* ===== CAROUSEL HERO ===== */
    .hero-carousel {
      position: relative;
      height: 52vh;
      min-height: 340px;
      overflow: hidden;
    }

    .carousel-slides {
      display: flex;
      height: 100%;
      transition: transform 0.7s cubic-bezier(0.77, 0, 0.175, 1);
    }

    .carousel-slide {
      flex: 0 0 100%;
      position: relative;
    }

    .carousel-slide img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      filter: brightness(0.45);
      display: block;
    }

    .carousel-caption {
      position: absolute;
      inset: 0;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      text-align: center;
      padding: 0 40px;
    }

    .carousel-caption .eyebrow {
      font-size: 0.7rem;
      font-weight: 500;
      letter-spacing: 0.25em;
      text-transform: uppercase;
      color: var(--gold);
      margin-bottom: 16px;
    }

    .carousel-caption h2 {
      font-family: 'Playfair Display', serif;
      font-size: clamp(1.8rem, 4vw, 3.2rem);
      font-weight: 400;
      color: #fff;
      line-height: 1.2;
      margin-bottom: 12px;
    }

    .carousel-caption p {
      font-size: 0.95rem;
      font-weight: 300;
      color: rgba(255,255,255,0.6);
    }

    .carousel-divider {
      width: 48px;
      height: 1px;
      background: var(--gold);
      margin: 20px auto 0;
    }

    .carousel-btn {
      position: absolute;
      top: 50%;
      transform: translateY(-50%);
      background: rgba(255,255,255,0.08);
      border: 1px solid rgba(255,255,255,0.2);
      color: #fff;
      width: 44px;
      height: 44px;
      border-radius: 50%;
      font-size: 1.2rem;
      cursor: pointer;
      transition: background 0.2s;
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 10;
    }

    .carousel-btn:hover { background: rgba(201,168,76,0.25); border-color: var(--gold); }
    .carousel-btn.prev { left: 24px; }
    .carousel-btn.next { right: 24px; }

    .carousel-dots {
      position: absolute;
      bottom: 20px;
      left: 50%;
      transform: translateX(-50%);
      display: flex;
      gap: 8px;
      z-index: 10;
    }

    .carousel-dot {
      width: 6px;
      height: 6px;
      border-radius: 50%;
      background: rgba(255,255,255,0.35);
      cursor: pointer;
      transition: background 0.2s, transform 0.2s;
      border: none;
    }

    .carousel-dot.active {
      background: var(--gold);
      transform: scale(1.3);
    }

    /* ===== FILTER BAR ===== */
    .filter-bar {
      background: var(--white);
      border-bottom: 1px solid var(--cream-dark);
      padding: 20px 40px;
      position: sticky;
      top: 70px;
      z-index: 50;
    }

    .filter-inner {
      max-width: 1200px;
      margin: 0 auto;
      display: flex;
      align-items: center;
      gap: 16px;
      flex-wrap: wrap;
    }

    .filter-search {
      flex: 1 1 260px;
      display: flex;
      border: 1px solid var(--cream-dark);
      border-radius: 3px;
      overflow: hidden;
      transition: border-color 0.2s;
    }

    .filter-search:focus-within { border-color: var(--navy-mid); }

    .filter-search input {
      flex: 1;
      border: none;
      padding: 9px 14px;
      font-family: 'DM Sans', sans-serif;
      font-size: 0.88rem;
      font-weight: 300;
      color: var(--text-body);
      background: transparent;
      outline: none;
    }

    .filter-search button {
      background: var(--navy);
      color: #fff;
      border: none;
      padding: 9px 16px;
      font-family: 'DM Sans', sans-serif;
      font-size: 0.82rem;
      font-weight: 500;
      cursor: pointer;
      transition: background 0.2s;
    }

    .filter-search button:hover { background: var(--gold); color: var(--navy); }

    .filter-count {
      font-size: 0.78rem;
      font-weight: 400;
      color: var(--text-muted);
      letter-spacing: 0.05em;
      margin-left: auto;
    }

    .filter-count strong { color: var(--navy); font-weight: 600; }

    /* ===== BOOKS SECTION ===== */
    .books-section {
      padding: 60px 40px 80px;
    }

    .books-header {
      display: flex;
      align-items: flex-end;
      justify-content: space-between;
      margin-bottom: 48px;
      gap: 20px;
      flex-wrap: wrap;
    }

    .books-header-left .eyebrow {
      font-size: 0.7rem;
      font-weight: 500;
      letter-spacing: 0.22em;
      text-transform: uppercase;
      color: var(--gold);
      margin-bottom: 10px;
    }

    .books-header-left h2 {
      font-family: 'Playfair Display', serif;
      font-size: 2.2rem;
      font-weight: 400;
      color: var(--navy);
      line-height: 1.2;
    }

    .view-toggle {
      display: flex;
      gap: 6px;
    }

    .view-btn {
      width: 36px;
      height: 36px;
      border: 1px solid var(--cream-dark);
      background: var(--white);
      border-radius: 3px;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.2s;
      color: var(--text-muted);
    }

    .view-btn.active, .view-btn:hover {
      background: var(--navy);
      border-color: var(--navy);
      color: #fff;
    }

    /* Grid view */
    .books-grid {
      display: grid;
      grid-template-columns: repeat(5, 1fr);
      gap: 24px;
    }

    .books-grid.list-view {
      grid-template-columns: 1fr;
      gap: 12px;
    }

    /* Book Card */
    .book-card {
      background: var(--white);
      border: 1px solid var(--cream-dark);
      border-radius: 3px;
      overflow: hidden;
      cursor: pointer;
      transition: transform 0.3s ease, box-shadow 0.3s ease, border-color 0.3s ease;
    }

    .book-card:hover {
      transform: translateY(-6px);
      box-shadow: 0 20px 48px rgba(13,31,78,0.1);
      border-color: rgba(201,168,76,0.4);
    }

    .book-card-img {
      position: relative;
      overflow: hidden;
    }

    .book-card-img img {
      width: 100%;
      height: 220px;
      object-fit: cover;
      display: block;
      filter: saturate(0.85);
      transition: transform 0.4s ease, filter 0.3s ease;
    }

    .book-card:hover .book-card-img img {
      transform: scale(1.05);
      filter: saturate(1);
    }

    .book-card-img .stok-badge {
      position: absolute;
      top: 10px;
      right: 10px;
      font-size: 0.62rem;
      font-weight: 600;
      letter-spacing: 0.08em;
      text-transform: uppercase;
      padding: 4px 8px;
      border-radius: 2px;
    }

    .stok-badge.tersedia { background: var(--navy); color: var(--gold-light); }
    .stok-badge.habis { background: rgba(0,0,0,0.5); color: rgba(255,255,255,0.6); }

    .book-card-body {
      padding: 16px;
    }

    .book-card-kategori {
      font-size: 0.62rem;
      font-weight: 500;
      letter-spacing: 0.12em;
      text-transform: uppercase;
      color: var(--gold);
      border: 1px solid rgba(201,168,76,0.35);
      padding: 2px 7px;
      border-radius: 2px;
      display: inline-block;
      margin-bottom: 8px;
    }

    .book-card-title {
      font-family: 'Playfair Display', serif;
      font-size: 0.95rem;
      font-weight: 400;
      color: var(--navy);
      line-height: 1.4;
      margin-bottom: 4px;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }

    .book-card-author {
      font-size: 0.78rem;
      font-weight: 300;
      color: var(--text-muted);
      margin-bottom: 12px;
    }

    .book-card-footer {
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .book-stok {
      font-size: 0.72rem;
      color: var(--text-muted);
      font-weight: 400;
    }

    .book-stok span { color: var(--navy-mid); font-weight: 600; }

    .btn-detail {
      font-family: 'DM Sans', sans-serif;
      font-size: 0.75rem;
      font-weight: 500;
      letter-spacing: 0.06em;
      color: var(--navy);
      border: 1px solid var(--cream-dark);
      background: transparent;
      padding: 5px 12px;
      border-radius: 2px;
      cursor: pointer;
      transition: all 0.2s;
    }

    .btn-detail:hover {
      background: var(--navy);
      color: #fff;
      border-color: var(--navy);
    }

    /* List view card */
    .books-grid.list-view .book-card {
      display: flex;
      transform: none !important;
    }

    .books-grid.list-view .book-card:hover {
      box-shadow: 0 4px 20px rgba(13,31,78,0.08);
      border-color: rgba(201,168,76,0.3);
    }

    .books-grid.list-view .book-card-img {
      flex: 0 0 100px;
    }

    .books-grid.list-view .book-card-img img {
      height: 100%;
      min-height: 120px;
    }

    .books-grid.list-view .book-card-body {
      flex: 1;
      display: flex;
      align-items: center;
      gap: 20px;
      padding: 16px 20px;
    }

    .books-grid.list-view .book-card-title {
      font-size: 1rem;
      -webkit-line-clamp: 1;
      flex: 1;
    }

    .books-grid.list-view .book-card-footer {
      flex-direction: row;
      align-items: center;
    }

    /* ===== MODAL ===== */
    .modal-overlay {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(13,31,78,0.6);
      z-index: 1000;
      align-items: center;
      justify-content: center;
      padding: 20px;
      backdrop-filter: blur(4px);
    }

    .modal-overlay.open { display: flex; }

    .modal-box {
      background: var(--white);
      border-radius: 4px;
      max-width: 780px;
      width: 100%;
      max-height: 90vh;
      overflow: hidden;
      display: flex;
      animation: modalIn 0.35s cubic-bezier(0.16,1,0.3,1);
    }

    @keyframes modalIn {
      from { opacity: 0; transform: translateY(32px) scale(0.97); }
      to   { opacity: 1; transform: translateY(0) scale(1); }
    }

    .modal-cover {
      flex: 0 0 280px;
      background: var(--navy);
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 40px 32px;
      position: relative;
      overflow: hidden;
    }

    .modal-cover::before {
      content: '';
      position: absolute;
      top: -40px; left: 50%;
      transform: translateX(-50%);
      width: 300px; height: 300px;
      border-radius: 50%;
      border: 1px solid rgba(201,168,76,0.1);
    }

    .modal-cover img {
      width: 100%;
      max-width: 180px;
      border-radius: 3px;
      box-shadow: 0 20px 40px rgba(0,0,0,0.4);
      position: relative;
      z-index: 1;
    }

    .modal-stock-badge {
      position: absolute;
      bottom: 20px;
      left: 50%;
      transform: translateX(-50%);
      font-size: 0.7rem;
      font-weight: 600;
      letter-spacing: 0.12em;
      text-transform: uppercase;
      padding: 6px 16px;
      border-radius: 2px;
      white-space: nowrap;
    }

    .modal-stock-badge.tersedia { background: rgba(201,168,76,0.15); color: var(--gold-light); border: 1px solid rgba(201,168,76,0.3); }
    .modal-stock-badge.habis { background: rgba(255,255,255,0.05); color: rgba(255,255,255,0.4); border: 1px solid rgba(255,255,255,0.1); }

    .modal-content {
      flex: 1;
      padding: 40px 36px;
      overflow-y: auto;
      display: flex;
      flex-direction: column;
      justify-content: center;
    }

    .modal-kategori {
      font-size: 0.65rem;
      font-weight: 500;
      letter-spacing: 0.18em;
      text-transform: uppercase;
      color: var(--gold);
      border: 1px solid rgba(201,168,76,0.35);
      padding: 3px 10px;
      border-radius: 2px;
      display: inline-block;
      margin-bottom: 16px;
    }

    .modal-title {
      font-family: 'Playfair Display', serif;
      font-size: 1.7rem;
      font-weight: 400;
      color: var(--navy);
      line-height: 1.25;
      margin-bottom: 8px;
    }

    .modal-author {
      font-size: 0.9rem;
      font-weight: 300;
      color: var(--text-muted);
      font-style: italic;
      margin-bottom: 28px;
    }

    .modal-divider {
      width: 40px;
      height: 1px;
      background: var(--gold);
      margin-bottom: 28px;
    }

    .modal-meta {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 12px;
      margin-bottom: 32px;
    }

    .meta-row {
      background: var(--cream);
      border: 1px solid var(--cream-dark);
      border-radius: 3px;
      padding: 12px 14px;
    }

    .meta-row .label {
      font-size: 0.65rem;
      font-weight: 500;
      letter-spacing: 0.12em;
      text-transform: uppercase;
      color: var(--text-muted);
      margin-bottom: 4px;
    }

    .meta-row .value {
      font-size: 0.88rem;
      font-weight: 500;
      color: var(--navy);
    }

    .modal-close {
      position: absolute;
      top: 16px;
      right: 16px;
      background: rgba(255,255,255,0.08);
      border: 1px solid rgba(255,255,255,0.15);
      color: rgba(255,255,255,0.6);
      width: 32px;
      height: 32px;
      border-radius: 50%;
      font-size: 1rem;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.2s;
      z-index: 10;
    }

    .modal-close:hover { background: rgba(255,255,255,0.15); color: #fff; }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 1024px) {
      .books-grid { grid-template-columns: repeat(4, 1fr); }
    }
    @media (max-width: 768px) {
      .books-grid { grid-template-columns: repeat(2, 1fr); gap: 16px; }
      .modal-box { flex-direction: column; max-height: 95vh; }
      .modal-cover { flex: 0 0 200px; }
      .container, .books-section, .filter-bar { padding-left: 20px; padding-right: 20px; }
      .modal-meta { grid-template-columns: 1fr; }
    }
    @media (max-width: 480px) {
      .books-grid { grid-template-columns: repeat(2, 1fr); gap: 10px; }
      .book-card-img img { height: 160px; }
    }
  </style>
</head>
<body>
  <?php include 'partials/header.php'; ?>

  <!-- CAROUSEL HERO -->
  <div class="hero-carousel" id="heroCarousel">
    <div class="carousel-slides" id="carouselSlides">
      <div class="carousel-slide">
        <img src="assets/img/baca2.png" alt="Slide 1">
        <div class="carousel-caption">
          <span class="eyebrow">Perpustakaan Digital Litera</span>
          <h2>Temukan buku<br><em>favoritmu hari ini</em></h2>
          <p>Jelajahi ribuan koleksi dari berbagai genre</p>
          <div class="carousel-divider"></div>
        </div>
      </div>
      <div class="carousel-slide">
        <img src="assets/img/baca4.jpg" alt="Slide 2">
        <div class="carousel-caption">
          <span class="eyebrow">Koleksi Terpilih</span>
          <h2>Baca kapan saja,<br><em>di mana saja</em></h2>
          <p>Update koleksi setiap minggu untuk pembaca setia</p>
          <div class="carousel-divider"></div>
        </div>
      </div>
    </div>
    <button class="carousel-btn prev" onclick="moveCarousel(-1)">&#8249;</button>
    <button class="carousel-btn next" onclick="moveCarousel(1)">&#8250;</button>
    <div class="carousel-dots" id="carouselDots">
      <button class="carousel-dot active" onclick="goToSlide(0)"></button>
      <button class="carousel-dot" onclick="goToSlide(1)"></button>
    </div>
  </div>

  <!-- FILTER BAR -->
  <div class="filter-bar">
    <div class="filter-inner">
      <div class="filter-search">
        <input type="text" id="searchInput" placeholder="Cari judul atau penulis..." oninput="filterBooks()">
        <button onclick="filterBooks()">Cari</button>
      </div>
      <span class="filter-count">
        Menampilkan <strong id="bookCount"><?= count($rows) ?></strong> buku
      </span>
    </div>
  </div>

  <!-- BOOKS SECTION -->
  <section class="books-section">
    <div class="container">
      <div class="books-header">
        <div class="books-header-left">
          <p class="eyebrow">Semua Koleksi</p>
          <h2>Daftar Buku Terbaru</h2>
        </div>
        <div class="view-toggle">
          <button class="view-btn active" id="gridBtn" onclick="setView('grid')" title="Grid view">
            <svg width="14" height="14" viewBox="0 0 16 16" fill="currentColor">
              <rect x="0" y="0" width="6" height="6"/><rect x="10" y="0" width="6" height="6"/>
              <rect x="0" y="10" width="6" height="6"/><rect x="10" y="10" width="6" height="6"/>
            </svg>
          </button>
          <button class="view-btn" id="listBtn" onclick="setView('list')" title="List view">
            <svg width="14" height="14" viewBox="0 0 16 16" fill="currentColor">
              <rect x="0" y="0" width="16" height="3"/><rect x="0" y="6" width="16" height="3"/>
              <rect x="0" y="12" width="16" height="3"/>
            </svg>
          </button>
        </div>
      </div>

      <div class="books-grid" id="booksGrid">
        <?php foreach ($rows as $buku): ?>
          <div class="book-card"
               data-judul="<?= strtolower(htmlspecialchars($buku['judul'])) ?>"
               data-penulis="<?= strtolower(htmlspecialchars($buku['penulis'])) ?>"
               onclick="openModal(
                 '<?= addslashes(htmlspecialchars($buku['judul'])) ?>',
                 '<?= addslashes(htmlspecialchars($buku['penulis'])) ?>',
                 '<?= addslashes(htmlspecialchars($buku['penerbit'] ?? '-')) ?>',
                 '<?= addslashes(htmlspecialchars($buku['tahun_terbit'] ?? '-')) ?>',
                 '<?= addslashes(htmlspecialchars($buku['nama_kategori'] ?? '-')) ?>',
                 '<?= htmlspecialchars($buku['gambar'] ?: 'default.png') ?>',
                 <?= (int)($buku['stok'] ?? 0) ?>
               )">
            <div class="book-card-img">
              <img src="uploads/<?= htmlspecialchars($buku['gambar'] ?: 'default.png') ?>"
                   alt="<?= htmlspecialchars($buku['judul']) ?>">
              <span class="stok-badge <?= ($buku['stok'] ?? 0) > 0 ? 'tersedia' : 'habis' ?>">
                <?= ($buku['stok'] ?? 0) > 0 ? 'Tersedia' : 'Habis' ?>
              </span>
            </div>
            <div class="book-card-body">
              <span class="book-card-kategori"><?= htmlspecialchars($buku['nama_kategori'] ?? '-') ?></span>
              <h3 class="book-card-title"><?= htmlspecialchars($buku['judul']) ?></h3>
              <p class="book-card-author"><?= htmlspecialchars($buku['penulis']) ?></p>
              <div class="book-card-footer">
                <span class="book-stok">Stok: <span><?= $buku['stok'] ?? 0 ?></span></span>
                <button class="btn-detail">Detail</button>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <div id="emptyState" style="display:none; text-align:center; padding:60px 20px;">
        <p style="font-family:'Playfair Display',serif; font-size:1.4rem; color:var(--navy); margin-bottom:8px;">Buku tidak ditemukan</p>
        <p style="font-size:0.88rem; color:var(--text-muted); font-weight:300;">Coba kata kunci yang berbeda</p>
      </div>
    </div>
  </section>

  <!-- MODAL -->
  <div class="modal-overlay" id="modalOverlay" onclick="closeModalOutside(event)">
    <div class="modal-box">
      <div class="modal-cover" style="position:relative;">
        <button class="modal-close" onclick="closeModal()">&#10005;</button>
        <img id="mImg" src="" alt="">
        <span class="modal-stock-badge" id="mStockBadge"></span>
      </div>
      <div class="modal-content">
        <span class="modal-kategori" id="mKategori"></span>
        <h2 class="modal-title" id="mJudul"></h2>
        <p class="modal-author" id="mPenulis"></p>
        <div class="modal-divider"></div>
        <div class="modal-meta">
          <div class="meta-row">
            <div class="label">Penerbit</div>
            <div class="value" id="mPenerbit"></div>
          </div>
          <div class="meta-row">
            <div class="label">Tahun Terbit</div>
            <div class="value" id="mTahun"></div>
          </div>
          <div class="meta-row">
            <div class="label">Kategori</div>
            <div class="value" id="mKategori2"></div>
          </div>
          <div class="meta-row">
            <div class="label">Stok Tersedia</div>
            <div class="value" id="mStok"></div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <?php include 'partials/footer.php'; ?>

  <script>
    // Carousel
    let current = 0;
    const total = 2;
    function moveCarousel(dir) {
      current = (current + dir + total) % total;
      goToSlide(current);
    }
    function goToSlide(i) {
      current = i;
      document.getElementById('carouselSlides').style.transform = `translateX(-${i * 100}%)`;
      document.querySelectorAll('.carousel-dot').forEach((d, idx) => d.classList.toggle('active', idx === i));
    }
    setInterval(() => moveCarousel(1), 5000);

    // View toggle
    function setView(v) {
      const grid = document.getElementById('booksGrid');
      grid.classList.toggle('list-view', v === 'list');
      document.getElementById('gridBtn').classList.toggle('active', v === 'grid');
      document.getElementById('listBtn').classList.toggle('active', v === 'list');
    }

    // Search/filter
    function filterBooks() {
      const q = document.getElementById('searchInput').value.toLowerCase();
      const cards = document.querySelectorAll('.book-card');
      let visible = 0;
      cards.forEach(c => {
        const match = c.dataset.judul.includes(q) || c.dataset.penulis.includes(q);
        c.style.display = match ? '' : 'none';
        if (match) visible++;
      });
      document.getElementById('bookCount').textContent = visible;
      document.getElementById('emptyState').style.display = visible === 0 ? 'block' : 'none';
    }

    // Modal
    function openModal(judul, penulis, penerbit, tahun, kategori, gambar, stok) {
      document.getElementById('mImg').src = 'uploads/' + gambar;
      document.getElementById('mJudul').textContent = judul;
      document.getElementById('mPenulis').textContent = 'oleh ' + penulis;
      document.getElementById('mKategori').textContent = kategori;
      document.getElementById('mKategori2').textContent = kategori;
      document.getElementById('mPenerbit').textContent = penerbit;
      document.getElementById('mTahun').textContent = tahun;
      document.getElementById('mStok').textContent = stok + ' eksemplar';
      const badge = document.getElementById('mStockBadge');
      badge.textContent = stok > 0 ? 'Tersedia' : 'Stok Habis';
      badge.className = 'modal-stock-badge ' + (stok > 0 ? 'tersedia' : 'habis');
      document.getElementById('modalOverlay').classList.add('open');
      document.body.style.overflow = 'hidden';
    }

    function closeModal() {
      document.getElementById('modalOverlay').classList.remove('open');
      document.body.style.overflow = '';
    }

    function closeModalOutside(e) {
      if (e.target === document.getElementById('modalOverlay')) closeModal();
    }

    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });

    // Fade in cards
    const obs = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.style.opacity = '1';
          entry.target.style.transform = 'translateY(0)';
          obs.unobserve(entry.target);
        }
      });
    }, { threshold: 0.08 });

    document.querySelectorAll('.book-card').forEach((el, i) => {
      el.style.opacity = '0';
      el.style.transform = 'translateY(20px)';
      el.style.transition = `opacity 0.5s ease ${(i % 5) * 0.07}s, transform 0.5s ease ${(i % 5) * 0.07}s`;
      obs.observe(el);
    });
  </script>
</body>
</html> 