<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

include '../config/config.php';

// Get parameters from URL
$keyword = isset($_GET['search']) ? trim($_GET['search']) : '';
$category_filter = isset($_GET['kategori']) ? (int)$_GET['kategori'] : 0;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 6; // Increased from 5 to 6 for better grid layout
$offset = ($page - 1) * $limit;

// Build WHERE clause for filtering
$where_conditions = [];
$params = [];

if (!empty($keyword)) {
    $where_conditions[] = "buku.judul LIKE ?";
    $params[] = "%{$keyword}%";
}

if ($category_filter > 0) {
    $where_conditions[] = "buku.id_kategori = ?";
    $params[] = $category_filter;
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Get total count for pagination
$count_query = "SELECT COUNT(*) as total FROM buku JOIN kategori ON buku.id_kategori = kategori.id_kategori $where_clause";
$count_stmt = mysqli_prepare($conn, $count_query);

if (!empty($params)) {
    $types = str_repeat('s', count($params));
    mysqli_stmt_bind_param($count_stmt, $types, ...$params);
}

mysqli_stmt_execute($count_stmt);
$total_data = mysqli_fetch_assoc(mysqli_stmt_get_result($count_stmt))['total'];
$total_pages = ceil($total_data / $limit);

// Get books with pagination
$main_query = "
    SELECT buku.*, kategori.nama_kategori 
    FROM buku 
    JOIN kategori ON buku.id_kategori = kategori.id_kategori 
    $where_clause 
    ORDER BY buku.judul ASC 
    LIMIT ? OFFSET ?
";

$stmt = mysqli_prepare($conn, $main_query);
$all_params = array_merge($params, [$limit, $offset]);
$types = str_repeat('s', count($params)) . 'ii';
mysqli_stmt_bind_param($stmt, $types, ...$all_params);
mysqli_stmt_execute($stmt);
$query_result = mysqli_stmt_get_result($stmt);

// Get all categories for filter dropdown
$categories_query = mysqli_query($conn, "SELECT * FROM kategori ORDER BY nama_kategori ASC");

// Handle book submission
$message = '';
if (isset($_GET['ajukan'])) {
    $id_buku = (int)$_GET['ajukan'];
    $id_user = $_SESSION['user_id'];
    
    // Check book stock
    $stock_stmt = mysqli_prepare($conn, "SELECT stok, judul FROM buku WHERE id_buku = ?");
    mysqli_stmt_bind_param($stock_stmt, 'i', $id_buku);
    mysqli_stmt_execute($stock_stmt);
    $book_data = mysqli_fetch_assoc(mysqli_stmt_get_result($stock_stmt));
    
    if (!$book_data) {
        $message = "<span class='alert alert-error'>Buku tidak ditemukan.</span>";
    } elseif ($book_data['stok'] <= 0) {
        $message = "<span class='alert alert-error'>Stok buku habis. Tidak bisa diajukan.</span>";
    } else {
        // Check if user has pending submission
        $pending_stmt = mysqli_prepare($conn, "SELECT id_pengajuan FROM pengajuan_buku WHERE id_user = ? AND status = 'pending' LIMIT 1");
        mysqli_stmt_bind_param($pending_stmt, 'i', $id_user);
        mysqli_stmt_execute($pending_stmt);
        $pending_result = mysqli_stmt_get_result($pending_stmt);
        
        if (mysqli_num_rows($pending_result) > 0) {
            $id_pengajuan = mysqli_fetch_assoc($pending_result)['id_pengajuan'];
        } else {
            // Create new submission
            $create_stmt = mysqli_prepare($conn, "INSERT INTO pengajuan_buku(id_user, tanggal_pengajuan, status) VALUES(?, NOW(), 'pending')");
            mysqli_stmt_bind_param($create_stmt, 'i', $id_user);
            mysqli_stmt_execute($create_stmt);
            $id_pengajuan = mysqli_insert_id($conn);
        }
        
        // Check if user already submitted this book title
        $check_stmt = mysqli_prepare($conn, "
            SELECT COUNT(*) as count
            FROM pengajuan_buku p
            JOIN detail_pengajuan_buku dp ON p.id_pengajuan = dp.id_pengajuan
            JOIN buku b ON dp.id_buku = b.id_buku
            WHERE p.id_user = ? AND b.judul = ? AND p.status = 'pending'
        ");
        mysqli_stmt_bind_param($check_stmt, 'is', $id_user, $book_data['judul']);
        mysqli_stmt_execute($check_stmt);
        $existing_count = mysqli_fetch_assoc(mysqli_stmt_get_result($check_stmt))['count'];
        
        if ($existing_count == 0) {
            // Add book to submission details
            $detail_stmt = mysqli_prepare($conn, "INSERT INTO detail_pengajuan_buku(id_pengajuan, id_buku) VALUES(?, ?)");
            mysqli_stmt_bind_param($detail_stmt, 'ii', $id_pengajuan, $id_buku);
            mysqli_stmt_execute($detail_stmt);
            
            $message = "<span class='alert alert-success'>Berhasil mengajukan buku '{$book_data['judul']}'.</span>";
        } else {
            $message = "<span class='alert alert-error'>Anda sudah mengajukan buku dengan judul yang sama.</span>";
        }
    }
}

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
            <h2>Daftar Buku</h2>
            
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
            </div>
            
            <!-- Message -->
            <?php if (!empty($message)): ?>
                <div class="message"><?php echo $message; ?></div>
            <?php endif; ?>

            
            <!-- Books Grid -->
            <?php if (mysqli_num_rows($query_result) > 0): ?>
                <ul class="buku-grid">
                    <?php while ($row = mysqli_fetch_assoc($query_result)): ?>
                        <li class="buku-card">
                            <?php if (!empty($row['gambar'])): ?>
                                <img src="../uploads/<?php echo htmlspecialchars($row['gambar']); ?>" 
                                     alt="Cover <?php echo htmlspecialchars($row['judul']); ?>">
                            <?php else: ?>
                                <img src="../assets/images/no-image.png" alt="No Image">
                            <?php endif; ?>
                            
                            <div class="buku-info">
                                <h3><?php echo htmlspecialchars($row['judul']); ?></h3>
                                <p class="kategori"><?php echo htmlspecialchars($row['nama_kategori']); ?></p>
                                <p><strong>Penulis:</strong> <?php echo htmlspecialchars($row['penulis']); ?></p>
                                <p><strong>Stok:</strong> <?php echo $row['stok']; ?> buku</p>
                                
                                <?php if ($row['stok'] > 0): ?>
                                    <a class="ajukan-link" 
                                       href="<?php echo buildUrl(['ajukan' => $row['id_buku']]); ?>" 
                                       onclick="return confirm('Ajukan buku &quot;<?php echo htmlspecialchars($row['judul']); ?>&quot;?')">
                                        Ajukan Buku
                                    </a>
                                <?php else: ?>
                                    <span class="stok-habis">Stok Habis</span>
                                <?php endif; ?>
                            </div>
                        </li>
                    <?php endwhile; ?>
                </ul>
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="<?php echo buildUrl(['page' => $page - 1]); ?>">&laquo; Sebelumnya</a>
                        <?php endif; ?>
                        
                        <?php
                        $start_page = max(1, $page - 2);
                        $end_page = min($total_pages, $page + 2);
                        
                        for ($i = $start_page; $i <= $end_page; $i++):
                        ?>
                            <?php if ($i == $page): ?>
                                <span class="current"><?php echo $i; ?></span>
                            <?php else: ?>
                                <a href="<?php echo buildUrl(['page' => $i]); ?>"><?php echo $i; ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
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