<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

include '../config/config.php';

$id_user = $_SESSION['user_id'];

$userRes = mysqli_query($conn, "SELECT kategori FROM users WHERE id_user = '$id_user'");
$user = mysqli_fetch_assoc($userRes);
$kategori = strtolower($user['kategori'] ?? '');

$MAX_PINJAM = 25;
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajukan'])) {
    $id_buku = (int) $_POST['ajukan'];
    $qty = isset($_POST['qty']) ? max(1, (int)$_POST['qty']) : 1;
    $qty = ($kategori === 'pengajar') ? $qty : 1;

    $bukuRes = mysqli_query($conn, "SELECT judul, stok FROM buku WHERE id_buku = '$id_buku'");
    $buku = mysqli_fetch_assoc($bukuRes);

    if (!$buku) {
        $message = "<span style='color:red;'>Buku tidak ditemukan.</span>";
    } elseif ($buku['stok'] < $qty) {
        $message = "<span style='color:red;'>Stok tidak mencukupi (tersedia {$buku['stok']}).</span>";
    } else {
        $totalRes = mysqli_query($conn, "SELECT COALESCE(SUM(dp.jumlah),0) AS total
                                         FROM pengajuan_buku p
                                         JOIN detail_pengajuan_buku dp ON p.id_pengajuan = dp.id_pengajuan
                                         WHERE p.id_user = '$id_user' AND p.status = 'pending'");
        $total = (int) mysqli_fetch_assoc($totalRes)['total'];

        $pendRes = mysqli_query($conn, "SELECT id_pengajuan FROM pengajuan_buku WHERE id_user = '$id_user' AND status = 'pending' LIMIT 1");
        if (mysqli_num_rows($pendRes) > 0) {
            $id_pengajuan = mysqli_fetch_assoc($pendRes)['id_pengajuan'];
        } else {
            mysqli_query($conn, "INSERT INTO pengajuan_buku (id_user, tanggal_pengajuan, status) VALUES ('$id_user', NOW(), 'pending')");
            $id_pengajuan = mysqli_insert_id($conn);
        }

        $checkDupe = mysqli_query($conn, "SELECT jumlah FROM detail_pengajuan_buku WHERE id_pengajuan = '$id_pengajuan' AND id_buku = '$id_buku'");
        if (mysqli_num_rows($checkDupe) > 0) {
            $prev = mysqli_fetch_assoc($checkDupe)['jumlah'];
            $newQty = $prev + $qty;
            if ($total - $prev + $newQty > $MAX_PINJAM) {
                $message = "<span style='color:red;'>Pengajuan gagal: melebihi batas $MAX_PINJAM buku.</span>";
            } else {
                mysqli_query($conn, "UPDATE detail_pengajuan_buku SET jumlah = '$newQty' WHERE id_pengajuan = '$id_pengajuan' AND id_buku = '$id_buku'");
                $_SESSION['message'] = "Jumlah buku '<strong>" . htmlspecialchars($buku['judul']) . "</strong>' diperbarui menjadi $newQty.";
                header("Location: daftar_buku.php");
                exit;
            }
        } else {
            if ($total + $qty > $MAX_PINJAM) {
                $message = "<span style='color:red;'>Pengajuan gagal: melebihi batas $MAX_PINJAM buku.</span>";
            } else {
                mysqli_query($conn, "INSERT INTO detail_pengajuan_buku (id_pengajuan, id_buku, jumlah) VALUES ('$id_pengajuan', '$id_buku', '$qty')");
                $_SESSION['message'] = "Berhasil mengajukan $qty buku '<strong>" . htmlspecialchars($buku['judul']) . "</strong>'.";
                header("Location: daftar_buku.php");
                exit;
            }
        }
    }
}

$keyword = $_GET['search'] ?? '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$sqlTotal = "SELECT COUNT(*) AS total FROM buku WHERE judul LIKE '%" . mysqli_real_escape_string($conn, $keyword) . "%'";
$totalData = (int) mysqli_fetch_assoc(mysqli_query($conn, $sqlTotal))['total'];
$totalPages = max(1, ceil($totalData / $limit));

$sqlBuku = "SELECT b.*, k.nama_kategori FROM buku b JOIN kategori k ON b.id_kategori = k.id_kategori
            WHERE b.judul LIKE '%" . mysqli_real_escape_string($conn, $keyword) . "%'
            ORDER BY b.judul ASC LIMIT $limit OFFSET $offset";
$booksRes = mysqli_query($conn, $sqlBuku);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Daftar Buku — Litera</title>
  <link rel="icon" href="assets/img/favicon-32x32.png" />
  <link rel="stylesheet" href="assets/css/header.css">
  <link rel="stylesheet" href="assets/css/daftar_buku.css">
  <link rel="stylesheet" href="assets/css/footer.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <script src="assets/js/header.js" defer></script>
</head>
<body>
  <?php include 'partials/header.php'; ?>

  <section class="buku-section">
    <div class="container">

      <p class="page-subtitle">Koleksi Perpustakaan Digital</p>
      <h1 class="page-title">Daftar Buku</h1>

      <!-- Filter -->
      <div class="filters-container">
        <form method="get">
          <div class="filters-row">
            <div class="filter-group">
              <label for="search">Cari Judul Buku</label>
              <input type="text" id="search" name="search"
                     value="<?= htmlspecialchars($keyword) ?>"
                     placeholder="Ketik judul buku...">
            </div>
            <div class="filter-group">
              <!-- kosong, bisa tambah filter kategori di sini -->
            </div>
            <div class="filter-buttons">
              <button type="submit" class="btn btn-primary">Cari</button>
              <a href="daftar_buku.php" class="btn btn-secondary">Reset</a>
            </div>
          </div>
        </form>
      </div>

      <!-- Message -->
      <?php if (!empty($message)): ?>
        <p class="message"><?= $message ?></p>
      <?php endif; ?>
      <?php if (isset($_SESSION['message'])): ?>
        <p class="message"><?= $_SESSION['message'] ?></p>
        <?php unset($_SESSION['message']); ?>
      <?php endif; ?>

      <!-- Results info -->
      <div class="results-info">
        <p>
          <?php if ($keyword): ?>
            Menampilkan <strong><?= $totalData ?></strong> hasil untuk "<em><?= htmlspecialchars($keyword) ?></em>"
          <?php else: ?>
            Menampilkan <strong><?= $totalData ?></strong> koleksi buku
          <?php endif; ?>
        </p>
      </div>

      <!-- Books Grid -->
      <?php if ($totalData > 0): ?>
        <ul class="buku-grid">
          <?php while ($b = mysqli_fetch_assoc($booksRes)): ?>
            <li class="buku-card">
              <img src="uploads/<?= htmlspecialchars($b['gambar'] ?: 'no-image.png') ?>"
                   alt="<?= htmlspecialchars($b['judul']) ?>">
              <div class="buku-info">
                <span class="kategori"><?= htmlspecialchars($b['nama_kategori']) ?></span>
                <h3><?= htmlspecialchars($b['judul']) ?></h3>
                <p><?= htmlspecialchars($b['penulis']) ?></p>
                <p>Stok: <strong><?= $b['stok'] ?></strong></p>

                <?php if ($b['stok'] > 0): ?>
                  <form class="ajukan-form" method="post"
                        onsubmit="return confirm('Ajukan buku ini?');">
                    <input type="hidden" name="ajukan" value="<?= $b['id_buku'] ?>">
                    <?php if ($kategori === 'pengajar'): ?>
                      <label>Jumlah</label>
                      <input type="number" name="qty" min="1"
                             max="<?= $b['stok'] ?>" value="1" required>
                    <?php endif; ?>
                    <button type="submit">Ajukan Buku</button>
                  </form>
                <?php else: ?>
                  <span class="stok-habis">Stok Habis</span>
                <?php endif; ?>
              </div>
            </li>
          <?php endwhile; ?>
        </ul>
      <?php else: ?>
        <div class="empty-state">
          <h3>Buku tidak ditemukan</h3>
          <p>Coba kata kunci yang berbeda atau <a href="daftar_buku.php">lihat semua koleksi</a>.</p>
        </div>
      <?php endif; ?>

      <!-- Pagination -->
      <?php if ($totalPages > 1): ?>
        <div class="pagination">
          <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?search=<?= urlencode($keyword) ?>&page=<?= $i ?>"
               class="<?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
          <?php endfor; ?>
        </div>
      <?php endif; ?>

      <div class="back-link">
        <a href="index.php">&larr; Kembali ke Beranda</a>
      </div>

    </div>
  </section>

  <?php include 'partials/footer.php'; ?>

</body>
</html>