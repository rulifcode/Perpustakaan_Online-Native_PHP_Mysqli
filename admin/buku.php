<?php
session_start();
include '../config/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// Ambil pesan dari session
$success = isset($_SESSION['success']) ? $_SESSION['success'] : '';
$error = isset($_SESSION['error']) ? $_SESSION['error'] : '';

// Hapus pesan dari session setelah diambil
unset($_SESSION['success']);
unset($_SESSION['error']);

// Ambil semua kategori
$query_kategori = "SELECT * FROM kategori ORDER BY nama_kategori ASC";
$result_kategori = mysqli_query($conn, $query_kategori);

// Cek filter kategori dan pencarian
$filter_kategori = isset($_GET['kategori']) ? intval($_GET['kategori']) : 0;
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

// Pagination settings
$items_per_page = 5; // Jumlah buku per halaman (dikurangi untuk testing)
$current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($current_page - 1) * $items_per_page;

$count_query = "
    SELECT COUNT(*) as total
    FROM buku 
    LEFT JOIN kategori ON buku.id_kategori = kategori.id_kategori
    WHERE 1=1
";

// Tambahkan filter kategori jika ada
if ($filter_kategori > 0) {
    $count_query .= " AND buku.id_kategori = $filter_kategori";
}

// Tambahkan kondisi pencarian jika ada
if (!empty($search)) {
    $count_query .= " AND (
        buku.judul LIKE '%$search%' OR 
        buku.penulis LIKE '%$search%' OR 
        buku.penerbit LIKE '%$search%' OR
        kategori.nama_kategori LIKE '%$search%'
    )";
}

$count_result = mysqli_query($conn, $count_query);
$total_buku = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_buku / $items_per_page);

//Query dasar untuk ambil data buku dengan pagination
$base_query = "
    SELECT buku.*, kategori.nama_kategori 
    FROM buku 
    LEFT JOIN kategori ON buku.id_kategori = kategori.id_kategori
    WHERE 1=1
";

//Tambahkan filter kategori jika ada
if ($filter_kategori > 0) {
    $base_query .= " AND buku.id_kategori = $filter_kategori";
}

//Tambahkan kondisi pencarian jika ada
if (!empty($search)) {
    $base_query .= " AND (
        buku.judul LIKE '%$search%' OR 
        buku.penulis LIKE '%$search%' OR 
        buku.penerbit LIKE '%$search%' OR
        kategori.nama_kategori LIKE '%$search%'
    )";
}

$base_query .= " ORDER BY buku.id_buku DESC LIMIT $items_per_page OFFSET $offset";

$result_buku = mysqli_query($conn, $base_query);

//Fungsi untuk membuat URL pagination
function buildPaginationUrl($page, $search = '', $filter_kategori = 0) {
    $params = [];
    if ($page > 1) $params['page'] = $page;
    if (!empty($search)) $params['search'] = $search;
    if ($filter_kategori > 0) $params['kategori'] = $filter_kategori;
    
    return 'buku.php' . (!empty($params) ? '?' . http_build_query($params) : '');
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>Daftar Buku - Litera</title>
    
    <link rel="stylesheet" href="../assets/css/admin1.css">
  <link rel="stylesheet" href="../assets/css/sidebar.css">
    <link rel="icon" type="image/png" href="../assets/img/favicon-32x32.png" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>

</head>

<body>
    <?php include 'header.php'; ?>

    <!-- Tambahkan wrapper agar sidebar dan main-content satu wadah -->
    <div class="wrapper">
        <?php include 'sidebar.php'; ?>

        <!-- Konten Utama -->
         
        <div class="main-content">
         <div class="card">
            <div class="page-header">
                <h2 class="dashboard-title">
                    <i class="fas fa-book"></i> Daftar Buku 
                    <span class="total-count">(<?= $total_buku ?> buku)</span>
                </h2>
                <a href="tambah_buku.php" class="button primary">
                    <i class="fas fa-plus"></i> Tambah Buku
                </a>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?= $success ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= $error ?>
                </div>
            <?php endif; ?>

            <!-- Form Pencarian -->
            <form method="GET" class="search-form">
                <div class="search-container">
                    <input type="text" name="search" placeholder="Cari judul, penulis, penerbit, atau kategori..." 
                        value="<?= htmlspecialchars($search) ?>" class="input-search">
                    <?php if ($filter_kategori > 0): ?>
                        <input type="hidden" name="kategori" value="<?= $filter_kategori ?>">
                    <?php endif; ?>
                </div>
                <div class="search-buttons">
                    <button type="submit" class="btn-search">Cari</button>
                    <a href="<?= $filter_kategori > 0 ? 'buku.php?kategori=' . $filter_kategori : 'buku.php' ?>" class="btn-reset">Reset</a>
                </div>
            </form>

            <!-- Filter Kategori -->
            <div class="kategori-filter">
                <strong><i class="fas fa-filter"></i> Filter Kategori:</strong>
                <div class="kategori-list">
                    <a href="buku.php<?= !empty($search) ? '?search=' . urlencode($search) : '' ?>" 
                       class="<?= $filter_kategori == 0 ? 'active' : '' ?>">
                        <i class="fas fa-list"></i> Semua
                    </a>
                    <?php mysqli_data_seek($result_kategori, 0); ?>
                    <?php while ($kat = mysqli_fetch_assoc($result_kategori)): ?>
                        <?php 
                        $kategori_url = 'buku.php?kategori=' . $kat['id_kategori'];
                        if (!empty($search)) {
                            $kategori_url .= '&search=' . urlencode($search);
                        }
                        ?>
                        <a href="<?= $kategori_url ?>"
                           class="<?= $filter_kategori == $kat['id_kategori'] ? 'active' : '' ?>">
                            <?= htmlspecialchars($kat['nama_kategori']) ?>
                        </a>
                    <?php endwhile; ?>
                </div>
            </div>
                    </div>
            <!-- Tabel Buku -->
              <div class="card">
            <div class="table-container">
                <?php if (mysqli_num_rows($result_buku) > 0): ?>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th width="50">No</th>
                                    <th width="80">Gambar</th>
                                    <th>Judul</th>
                                    <th>Penulis</th>
                                    <th>Penerbit</th>
                                    <th width="120">Tahun Terbit</th>
                                    <th width="80">Stok</th>
                                    <th width="120">Kategori</th>
                                    <th width="150">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = $offset + 1; ?>
                                <?php while ($row = mysqli_fetch_assoc($result_buku)): ?>
                                <tr>
                                    <td class="text-center"><strong><?= $no++ ?></strong></td>
                                    <td class="text-center">
                                        <?php if (!empty($row['gambar'])): ?>
                                            <img src="../uploads/<?= $row['gambar'] ?>" 
                                                 alt="<?= htmlspecialchars($row['judul']) ?>"
                                                 class="book-thumbnail"
                                                 onclick="showImageModal('<?= '../uploads/' . $row['gambar'] ?>', '<?= htmlspecialchars($row['judul']) ?>')">
                                        <?php else: ?>
                                            <div class="no-image">
                                                <i class="fas fa-book"></i>
                                                <small>No Image</small>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="book-title">
                                            <strong><?= htmlspecialchars($row['judul']) ?></strong>
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars($row['penulis']) ?></td>
                                    <td><?= htmlspecialchars($row['penerbit']) ?></td>
                                    <td class="text-center"><?= date('Y', strtotime($row['tahun_terbit'])) ?></td>
                                    <td class="text-center">
                                        <span class="badge <?= $row['stok'] > 0 ? 'badge-success' : 'badge-danger' ?>">
                                            <?= $row['stok'] ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="kategori-badge">
                                            <?= htmlspecialchars($row['nama_kategori'] ?? 'Tidak Ada Kategori') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="aksi-buttons">
                                            <a href="edit_buku.php?id=<?= $row['id_buku'] ?>" 
                                               class="button edit" 
                                               title="Edit Buku">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <a href="buku_hapus.php?id=<?= $row['id_buku'] ?>" 
                                               class="button delete" 
                                               onclick="return confirm('Yakin ingin menghapus buku \'<?= htmlspecialchars(str_replace("'", "\\'", $row['judul'])) ?>\'?\n\nData yang sudah dihapus tidak dapat dikembalikan!');" 
                                               title="Hapus Buku">
                                                <i class="fas fa-trash"></i> Hapus
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <div class="pagination-container">
                            <div class="pagination-info">
                                Menampilkan <?= $offset + 1 ?> - <?= min($offset + $items_per_page, $total_buku) ?> dari <?= $total_buku ?> buku
                            </div>
                            
                            <div class="pagination">
                                <!-- Previous Button -->
                                <?php if ($current_page > 1): ?>
                                    <a href="<?= buildPaginationUrl($current_page - 1, $search, $filter_kategori) ?>" 
                                       class="prev-next" title="Halaman Sebelumnya">
                                        <i class="fas fa-chevron-left"></i> Previous
                                    </a>
                                <?php else: ?>
                                    <span class="prev-next disabled">
                                        <i class="fas fa-chevron-left"></i> Previous
                                    </span>
                                <?php endif; ?>

                                <!-- Page Numbers -->
                                <?php
                                $start_page = max(1, $current_page - 2);
                                $end_page = min($total_pages, $current_page + 2);
                                
                                // Show first page if not in range
                                if ($start_page > 1): ?>
                                    <a href="<?= buildPaginationUrl(1, $search, $filter_kategori) ?>">1</a>
                                    <?php if ($start_page > 2): ?>
                                        <span>...</span>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <!-- Page number links -->
                                <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                                    <?php if ($i == $current_page): ?>
                                        <span class="current"><?= $i ?></span>
                                    <?php else: ?>
                                        <a href="<?= buildPaginationUrl($i, $search, $filter_kategori) ?>"><?= $i ?></a>
                                    <?php endif; ?>
                                <?php endfor; ?>

                                <!-- Show last page if not in range -->
                                <?php if ($end_page < $total_pages): ?>
                                    <?php if ($end_page < $total_pages - 1): ?>
                                        <span>...</span>
                                    <?php endif; ?>
                                    <a href="<?= buildPaginationUrl($total_pages, $search, $filter_kategori) ?>"><?= $total_pages ?></a>
                                <?php endif; ?>

                                <!-- Next Button -->
                                <?php if ($current_page < $total_pages): ?>
                                    <a href="<?= buildPaginationUrl($current_page + 1, $search, $filter_kategori) ?>" 
                                       class="prev-next" title="Halaman Selanjutnya">
                                        Next <i class="fas fa-chevron-right"></i>
                                    </a>
                                <?php else: ?>
                                    <span class="prev-next disabled">
                                        Next <i class="fas fa-chevron-right"></i>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-book-open"></i>
                        <h3>
                            <?php if (!empty($search)): ?>
                                Tidak Ditemukan Hasil Pencarian
                            <?php elseif ($filter_kategori > 0): ?>
                                Tidak Ada Buku di Kategori Ini
                            <?php else: ?>
                                Belum Ada Data Buku
                            <?php endif; ?>
                        </h3>
                        <p>
                            <?php if (!empty($search)): ?>
                                Tidak ada buku yang sesuai dengan kata kunci "<?= htmlspecialchars($search) ?>".
                                <br>Coba gunakan kata kunci yang berbeda atau hapus filter pencarian.
                            <?php elseif ($filter_kategori > 0): ?>
                                Belum ada buku yang tersedia untuk kategori yang dipilih.
                            <?php else: ?>
                                Silakan tambahkan buku pertama Anda dengan mengklik tombol "Tambah Buku" di atas.
                            <?php endif; ?>
                        </p>
                        <?php if (!empty($search)): ?>
                            <a href="<?= $filter_kategori > 0 ? 'buku.php?kategori=' . $filter_kategori : 'buku.php' ?>" class="button secondary">
                                <i class="fas fa-times"></i> Hapus Pencarian
                            </a>
                        <?php elseif ($filter_kategori == 0): ?>
                            <a href="tambah_buku.php" class="button primary">
                                <i class="fas fa-plus"></i> Tambah Buku Pertama
                            </a>
                        <?php else: ?>
                            <a href="buku.php" class="button secondary">
                                <i class="fas fa-list"></i> Lihat Semua Buku
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        </div> 
    </div> <!-- End of wrapper -->
                        </div>
    <!-- Modal untuk menampilkan gambar besar -->
    <div id="imageModal" class="modal" onclick="closeImageModal()">
        <div class="modal-content">
            <span class="close">&times;</span>
            <img id="modalImage" src="" alt="">
            <div id="modalCaption"></div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        &copy; <?= date('Y') ?> Perpustakaan - Litera
    </footer>

    <script>
        // Fungsi untuk menampilkan modal gambar
        function showImageModal(imageSrc, caption) {
            const modal = document.getElementById("imageModal");
            const modalImg = document.getElementById("modalImage");
            const modalCaption = document.getElementById("modalCaption");
            
            modal.style.display = "block";
            modalImg.src = imageSrc;
            modalCaption.innerHTML = caption;
        }

        // Fungsi untuk menutup modal
        function closeImageModal() {
            document.getElementById("imageModal").style.display = "none";
        }

        // Tutup modal jika user menekan ESC
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeImageModal();
            }
        });

        // Auto hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                alert.style.opacity = '0';
                alert.style.transition = 'opacity 0.5s ease';
                setTimeout(function() {
                    alert.style.display = 'none';
                }, 500);
            });
        }, 5000);

                 const hamburger = document.getElementById('hamburger');
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');

        function toggleSidebar() {
            hamburger.classList.toggle('active');
            sidebar.classList.toggle('active');
            sidebarOverlay.classList.toggle('active');
            
            // Prevent body scroll when sidebar is open
            if (sidebar.classList.contains('active')) {
                document.body.style.overflow = 'hidden';
            } else {
                document.body.style.overflow = '';
            }
        }

        function closeSidebar() {
            hamburger.classList.remove('active');
            sidebar.classList.remove('active');
            sidebarOverlay.classList.remove('active');
            document.body.style.overflow = '';
        }

        // Event listeners
        hamburger.addEventListener('click', toggleSidebar);
        sidebarOverlay.addEventListener('click', closeSidebar);

        // Close sidebar when clicking on menu items (mobile)
        const sidebarLinks = sidebar.querySelectorAll('a');
        sidebarLinks.forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth <= 768) {
                    closeSidebar();
                }
            });
        });

        // Handle window resize
        window.addEventListener('resize', () => {
            if (window.innerWidth > 768) {
                closeSidebar();
            }
        });

        // Prevent scroll on touch devices when sidebar is open
        let touchStartY = 0;
        
        document.addEventListener('touchstart', (e) => {
            touchStartY = e.touches[0].clientY;
        });

        document.addEventListener('touchmove', (e) => {
            if (sidebar.classList.contains('active') && !sidebar.contains(e.target)) {
                const touchY = e.touches[0].clientY;
                const touchDelta = touchStartY - touchY;
                
                // Prevent scroll if not scrolling within sidebar
                if (Math.abs(touchDelta) > 5) {
                    e.preventDefault();
                }
            }
        }, { passive: false });
    </script>
</body>
</html>