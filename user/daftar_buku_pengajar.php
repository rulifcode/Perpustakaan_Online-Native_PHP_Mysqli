<?php
session_start();

// Redirect ke halaman login jika belum login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

include '../config/config.php';

$id_user = $_SESSION['user_id'];

// Ambil kategori user
$userRes = mysqli_query($conn, "SELECT kategori FROM users WHERE id_user = '$id_user'");
$user = mysqli_fetch_assoc($userRes);
$kategori = strtolower($user['kategori'] ?? '');

// Hanya izinkan guru/dosen
if (!in_array($kategori, ['pengajar'])) {
    header('Location: index.php');
    exit;
}

// Konstanta dan variabel utilitas
$MAX_PINJAM = 25;
$message = '';

// ============================================
// PROSES PENGAJUAN BUKU (GET method)
// ============================================
if (isset($_GET['ajukan'])) {
    $id_buku = (int) $_GET['ajukan'];
    $qty = isset($_GET['qty']) ? max(1, (int) $_GET['qty']) : 1;

    // Cek buku & stok
    $bukuRes = mysqli_query($conn, "SELECT judul, stok FROM buku WHERE id_buku = '$id_buku'");
    $buku = mysqli_fetch_assoc($bukuRes);

    if (!$buku) {
        $message = "<span class='alert alert-error'>Buku tidak ditemukan.</span>";
    } elseif ($buku['stok'] < $qty) {
        $message = "<span class='alert alert-error'>Stok tidak mencukupi (tersedia {$buku['stok']}).</span>";
    } else {
        // Hitung total buku pending yang sudah diajukan user ini
        $totalRes = mysqli_query($conn, "
            SELECT COALESCE(SUM(dp.jumlah), 0) AS total
            FROM pengajuan_buku p
            JOIN detail_pengajuan_buku dp ON p.id_pengajuan = dp.id_pengajuan
            WHERE p.id_user = '$id_user' AND p.status = 'pending'
        ");
        $total = (int) mysqli_fetch_assoc($totalRes)['total'];

        if ($total + $qty > $MAX_PINJAM) {
            $message = "<span class='alert alert-error'>Pengajuan gagal: melebihi batas $MAX_PINJAM buku (sudah diajukan $total).</span>";
        } else {
            // Pastikan sudah ada catatan pengajuan status pending
            $pendRes = mysqli_query($conn, "
                SELECT id_pengajuan 
                FROM pengajuan_buku 
                WHERE id_user = '$id_user' AND status = 'pending' 
                LIMIT 1
            ");
            
            if (mysqli_num_rows($pendRes) > 0) {
                $pend = mysqli_fetch_assoc($pendRes);
                $id_pengajuan = $pend['id_pengajuan'];
            } else {
                mysqli_query($conn, "
                    INSERT INTO pengajuan_buku (id_user, tanggal_pengajuan, status) 
                    VALUES ('$id_user', NOW(), 'pending')
                ");
                $id_pengajuan = mysqli_insert_id($conn);
            }

            // Simpan detail pengajuan
            $insDetail = mysqli_query($conn, "
                INSERT INTO detail_pengajuan_buku (id_pengajuan, id_buku, jumlah) 
                VALUES ('$id_pengajuan', '$id_buku', '$qty')
            ");

            if ($insDetail) {
                $message = "<span class='alert alert-success'>Berhasil mengajukan $qty eksemplar buku '<strong>" . htmlspecialchars($buku['judul']) . "</strong>'.</span>";
            } else {
                $message = "<span class='alert alert-error'>Gagal mengajukan buku.</span>";
            }
        }
    }
}

// ============================================
// PENCARIAN & PAGINATION LIST BUKU
// ============================================
$keyword = isset($_GET['search']) ? trim($_GET['search']) : '';
$category_filter = isset($_GET['kategori']) ? (int)$_GET['kategori'] : 0;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$limit = 6; // Changed from 5 to 6 for better grid layout
$offset = ($page - 1) * $limit;

// Build WHERE clause for filtering
$where_conditions = [];
if (!empty($keyword)) {
    $where_conditions[] = "b.judul LIKE '%" . mysqli_real_escape_string($conn, $keyword) . "%'";
}
if ($category_filter > 0) {
    $where_conditions[] = "b.id_kategori = '$category_filter'";
}
$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Total buku untuk pagination
$sqlTotal = "
    SELECT COUNT(*) AS total 
    FROM buku b
    JOIN kategori k ON b.id_kategori = k.id_kategori
    $where_clause
";
$totalData = (int) mysqli_fetch_assoc(mysqli_query($conn, $sqlTotal))['total'];
$totalPages = max(1, ceil($totalData / $limit));

// Ambil list buku tampil
$sqlBuku = "
    SELECT b.*, k.nama_kategori
    FROM buku b
    JOIN kategori k ON b.id_kategori = k.id_kategori
    $where_clause
    ORDER BY b.judul ASC
    LIMIT $limit OFFSET $offset
";
$booksRes = mysqli_query($conn, $sqlBuku);

// Get all categories for filter dropdown
$categories_query = mysqli_query($conn, "SELECT * FROM kategori ORDER BY nama_kategori ASC");

// Build current URL for pagination
function buildUrl($params = []) {
    $current_params = $_GET;
    unset($current_params['ajukan']); // Remove ajukan parameter
    $merged_params = array_merge($current_params, $params);
    return '?' . http_build_query(array_filter($merged_params));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Buku - Litera</title>
    <link rel="icon" type="image/png" href="../assets/img/favicon-32x32.png" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/daftar_buku.css">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="../assets/css/header.css">
    <link rel="stylesheet" href="../assets/css/footer.css">
    <script src="../assets/js/header.js"></script>
    <script src="../assets/js/animasi.js" defer></script>
    <script src="../assets/js/scroll.js" defer></script>
</head>
<body>
    <?php include '../partials/header_user.php'; ?>
    
    <section class="buku-section">
        <div class="container">
            
            <h2>Daftar Buku untuk Pengajar</h2>
            
            <!-- Filters -->
            <div class="filters-container">
                <form method="get" class="filters-form">
                    <div class="filters-row">
                        <div class="filter-group">
                            <label for="search">Cari Buku</label>
                            <input type="text" 
                                   id="search" 
                                   name="search" 
                                   placeholder="Masukkan judul buku..." 
                                   value="<?php echo htmlspecialchars($keyword); ?>">
                        </div>
                        
                        <div class="filter-group">
                            <label for="kategori">Kategori</label>
                            <select id="kategori" name="kategori">
                                <option value="">Semua Kategori</option>
                                <?php while ($cat = mysqli_fetch_assoc($categories_query)): ?>
                                    <option value="<?php echo $cat['id_kategori']; ?>" 
                                            <?php echo ($category_filter == $cat['id_kategori']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat['nama_kategori']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div class="filter-buttons">
                            <button type="submit" class="btn btn-primary">Cari</button>
                            <a href="<?php echo strtok($_SERVER['REQUEST_URI'], '?'); ?>" class="btn btn-secondary">Reset</a>
                        </div>
                    </div>
                </form>
            
            
            <!-- Message -->
            <?php if (!empty($message)): ?>
                <div class="message"><?php echo $message; ?></div>
            <?php endif; ?>
            </div>
            
            <!-- Books Grid -->
            <?php if (mysqli_num_rows($booksRes) > 0): ?>
                <ul class="buku-grid">
                    <?php while ($buku = mysqli_fetch_assoc($booksRes)): ?>
                        <li class="buku-card">
                            <?php if (!empty($buku['gambar'])): ?>
                                <img src="../uploads/<?php echo htmlspecialchars($buku['gambar']); ?>" 
                                     alt="Cover <?php echo htmlspecialchars($buku['judul']); ?>">
                            <?php else: ?>
                                <img src="../assets/images/no-image.png" alt="No Image">
                            <?php endif; ?>

                            <div class="buku-info">
                                <h3><?php echo htmlspecialchars($buku['judul']); ?></h3>
                                <p class="kategori"><?php echo htmlspecialchars($buku['nama_kategori']); ?></p>
                                <p><strong>Penulis:</strong> <?php echo htmlspecialchars($buku['penulis']); ?></p>
                                <p><strong>Stok:</strong> <?php echo (int) $buku['stok']; ?> buku</p>
                                
                                <?php if ($buku['stok'] > 0): ?>
                                    <form class="ajukan-form" method="get" onsubmit="return confirm('Ajukan buku ini?');">
                                        <input type="hidden" name="search" value="<?php echo htmlspecialchars($keyword); ?>">
                                        <input type="hidden" name="kategori" value="<?php echo $category_filter; ?>">
                                        <input type="hidden" name="page" value="<?php echo $page; ?>">
                                        <input type="hidden" name="ajukan" value="<?php echo $buku['id_buku']; ?>">
                                        
                                        <div class="qty-group">
                                            <label for="qty_<?php echo $buku['id_buku']; ?>">Jumlah:</label>
                                            <input type="number" 
                                                   id="qty_<?php echo $buku['id_buku']; ?>"
                                                   name="qty" 
                                                   min="1" 
                                                   max="<?php echo $buku['stok']; ?>" 
                                                   value="1" 
                                                   required>
                                        </div>
                                        <button type="submit" class="ajukan-link">Ajukan Buku</button>
                                    </form>
                                <?php else: ?>
                                    <span class="stok-habis">Stok Habis</span>
                                <?php endif; ?>
                            </div>
                        </li>
                    <?php endwhile; ?>
                </ul>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="<?php echo buildUrl(['page' => $page - 1]); ?>">&laquo; Sebelumnya</a>
                        <?php endif; ?>
                        
                        <?php
                        $start_page = max(1, $page - 2);
                        $end_page = min($totalPages, $page + 2);
                        
                        for ($i = $start_page; $i <= $end_page; $i++):
                        ?>
                            <?php if ($i == $page): ?>
                                <span class="current"><?php echo $i; ?></span>
                            <?php else: ?>
                                <a href="<?php echo buildUrl(['page' => $i]); ?>"><?php echo $i; ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>
                        
                        <?php if ($page < $totalPages): ?>
                            <a href="<?php echo buildUrl(['page' => $page + 1]); ?>">Selanjutnya &raquo;</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
            <?php else: ?>
                <div class="empty-state">
                    <h3>Tidak ada buku ditemukan</h3>
                    <p>Coba ubah kata kunci pencarian atau filter kategori Anda.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <?php include '../partials/footer.php'; ?>
    
    <script src="../assets/js/scroll.js"></script>
    <script src="../assets/js/animasi.js"></script>
    <script src="../assets/js/hero-anim.js"></script>
    <script src="https://unpkg.com/scrollreveal"></script>
    <script src="../assets/js/scroll-anim.js"></script>
    <script>
        const revealItems = document.querySelectorAll('.card, .kategori-card, .edukasi-content article');

        const revealOnScroll = () => {
            revealItems.forEach(item => {
                const rect = item.getBoundingClientRect();
                const isVisible = rect.top < window.innerHeight - 100;
                if (isVisible) {
                    item.style.opacity = 1;
                    item.style.transform = 'translateY(0)';
                }
            });
        };

        window.addEventListener('scroll', revealOnScroll);
        window.addEventListener('load', revealOnScroll);
    </script>
</body>
</html>