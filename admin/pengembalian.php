<?php
session_start();
include '../config/config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// Proses pengembalian
if (isset($_POST['kembalikan'])) {
    $id_peminjaman = mysqli_real_escape_string($conn, $_POST['id_peminjaman']);
    $tanggal_pengembalian = mysqli_real_escape_string($conn, $_POST['tanggal_pengembalian']);

    $cek = mysqli_query($conn, "SELECT * FROM pengembalian WHERE id_peminjaman = '$id_peminjaman'");
    if (mysqli_num_rows($cek) > 0) {
        echo "<script>alert('Peminjaman ini sudah dikembalikan.'); location.href='pengembalian.php';</script>";
        exit;
    }

    $data = mysqli_fetch_assoc(mysqli_query($conn, "
        SELECT p.tanggal_kembali, p.id_pengajuan, p.id_buku 
        FROM peminjaman p
        WHERE p.id_peminjaman = '$id_peminjaman'
    "));

    $jatuh_tempo = $data['tanggal_kembali'];
    $id_buku = $data['id_buku'];
    $id_pengajuan = $data['id_pengajuan'];

    // Ambil nilai denda dari database setting_denda berdasarkan kondisi keterlambatan
    $denda = 0;
    $status_lunas = 'lunas'; // Default lunas jika tidak ada denda

    if (strtotime($tanggal_pengembalian) > strtotime($jatuh_tempo)) {
        $selisih_hari = ceil((strtotime($tanggal_pengembalian) - strtotime($jatuh_tempo)) / 86400);
        
        // Ambil kategori user untuk menentukan jenis denda
        $pengajuan = mysqli_fetch_assoc(mysqli_query($conn, "
            SELECT u.kategori
            FROM pengajuan_buku pb
            JOIN users u ON pb.id_user = u.id_user
            WHERE pb.id_pengajuan = '$id_pengajuan'
        "));
        $kategori_user = strtolower($pengajuan['kategori']);

        // Ambil setting denda dari database
        if (in_array($kategori_user, ['pengajar'])) {
            // Untuk pengajar, ambil nilai denda pengajar dari database
            $setting_denda = mysqli_fetch_assoc(mysqli_query($conn, "
                SELECT hari_batas_pengajar, nominal_per_buku_pengajar 
                FROM setting_denda 
                LIMIT 1
            "));
            
            if ($setting_denda) {
                // PERBAIKAN: Denda per buku (tidak dikali jumlah total buku)
                // Karena pengembalian dilakukan satu per satu buku
                $denda = $selisih_hari * $setting_denda['nominal_per_buku_pengajar'];
            }
        } else {
            // Untuk pelajar/umum, ambil nilai denda pelajar dari database
            $setting_denda = mysqli_fetch_assoc(mysqli_query($conn, "
                SELECT hari_batas, nominal_perhari 
                FROM setting_denda 
                LIMIT 1
            "));
            
            if ($setting_denda) {
                $denda = $selisih_hari * $setting_denda['nominal_perhari'];
            }
        }

        // Set status lunas berdasarkan denda
        $status_lunas = ($denda > 0) ? 'belum lunas' : 'lunas';
    }

    // Insert ke tabel pengembalian
    mysqli_query($conn, "INSERT INTO pengembalian (id_peminjaman, tanggal_pengembalian, denda, status_lunas) 
                         VALUES ('$id_peminjaman', '$tanggal_pengembalian', '$denda', '$status_lunas')");
    
    // Update status peminjaman dan stok buku
    mysqli_query($conn, "UPDATE peminjaman SET status='dikembalikan' WHERE id_peminjaman='$id_peminjaman'");
    mysqli_query($conn, "UPDATE buku SET stok = stok + 1 WHERE id_buku = '$id_buku'");

    $status_message = ($denda == 0) ? 'Buku berhasil dikembalikan tepat waktu.' : 'Buku berhasil dikembalikan. Denda: Rp' . number_format($denda, 0, ',', '.');
    echo "<script>alert('$status_message'); location.href='pengembalian.php';</script>";
    exit;
}

// Filter kategori dan pencarian
$kategori_filter = $_GET['kategori'] ?? 'semua';
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

// Pagination settings
$records_per_page = 10;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$current_page = max(1, $current_page); // Pastikan minimal halaman 1
$offset = ($current_page - 1) * $records_per_page;

$kategori_sql = '';
if ($kategori_filter === 'pelajar') {
    $kategori_sql = "AND u.kategori IN ('umum', 'pelajar')";
} elseif ($kategori_filter === 'pengajar') {
    $kategori_sql = "AND u.kategori IN ('pengajar')";
}

// Query untuk dropdown peminjaman
$peminjaman_dropdown = mysqli_query($conn, "
    SELECT DISTINCT p.id_peminjaman, u.username, u.kategori, b.judul, p.tanggal_kembali
    FROM peminjaman p
    JOIN pengajuan_buku pb ON p.id_pengajuan = pb.id_pengajuan
    JOIN users u ON pb.id_user = u.id_user
    JOIN detail_pengajuan_buku dp ON pb.id_pengajuan = dp.id_pengajuan AND dp.id_buku = p.id_buku
    JOIN buku b ON dp.id_buku = b.id_buku
    WHERE p.status = 'dipinjam'
    AND NOT EXISTS (
        SELECT 1 FROM pengembalian k WHERE k.id_peminjaman = p.id_peminjaman
    )
    $kategori_sql
");

// Query untuk laporan dengan pencarian
$laporan_base_query = "
    SELECT 
        k.id_pengembalian,
        p.id_peminjaman, 
        u.username,
        u.kategori,
        b.judul, 
        pb.tanggal_pengajuan,
        p.tanggal_kembali, 
        k.tanggal_pengembalian, 
        k.denda,
        k.status_lunas,
        jumlah_buku.jumlah AS jumlah_buku
    FROM pengembalian k
    JOIN peminjaman p ON k.id_peminjaman = p.id_peminjaman
    JOIN pengajuan_buku pb ON p.id_pengajuan = pb.id_pengajuan
    JOIN users u ON pb.id_user = u.id_user
    JOIN buku b ON p.id_buku = b.id_buku
    JOIN (
        SELECT dp.id_pengajuan, SUM(dp.jumlah) AS jumlah
        FROM detail_pengajuan_buku dp
        GROUP BY dp.id_pengajuan
    ) AS jumlah_buku ON jumlah_buku.id_pengajuan = pb.id_pengajuan
    WHERE 1=1
    $kategori_sql
";

// Tambahkan kondisi pencarian jika ada
if (!empty($search)) {
    $laporan_base_query .= " AND (
        p.id_peminjaman LIKE '%$search%' OR 
        u.username LIKE '%$search%' OR 
        b.judul LIKE '%$search%' OR
        u.kategori LIKE '%$search%' OR
        k.status_lunas LIKE '%$search%'
    )";
}

// Hitung total data untuk pagination
$count_query = "SELECT COUNT(*) as total FROM (" . $laporan_base_query . ") as count_table";
$count_result = mysqli_query($conn, $count_query);
$total_records = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_records / $records_per_page);

// Query dengan LIMIT untuk pagination
$laporan_base_query .= " ORDER BY k.tanggal_pengembalian DESC LIMIT $offset, $records_per_page";
$laporan_query = mysqli_query($conn, $laporan_base_query);

// Function untuk membuat URL dengan parameter yang ada
function build_url($page) {
    global $kategori_filter, $search;
    $params = array();
    
    if ($kategori_filter !== 'semua') {
        $params['kategori'] = $kategori_filter;
    }
    if (!empty($search)) {
        $params['search'] = $search;
    }
    $params['page'] = $page;
    
    return 'pengembalian.php?' . http_build_query($params);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">    
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengembalian Buku - Litera</title>
    <link rel="stylesheet" href="../assets/css/pengembalian.css">
  <link rel="stylesheet" href="../assets/css/sidebar.css">
    <link rel="stylesheet" href="../assets/css/admin1.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="wrapper">
        <?php include 'sidebar.php'; ?>

        <div class="main-content">
           <div class="card">
            <div class="page-header">
                <h2 class="dashboard-title">
                    <i class="fas fa-undo"></i> Pengembalian Buku 
                    <span class="total-count">(<?= $total_records ?> data)</span>
                </h2>
            </div>
                    <div class="card-header">
                        <h3><i class="fas fa-book-reader"></i> Form Pengembalian</h3>
                    </div>
                    <div class="card-body">
                        <!-- Filter Kategori -->
                        <form method="GET" class="form-row mb-3">
                            <div class="filter-group">
                                <label for="kategori"><i class="fas fa-filter"></i> Filter Kategori:</label>
                                <select name="kategori" id="kategori" onchange="this.form.submit()" class="form-select">
                                    <option value="semua" <?= $kategori_filter === 'semua' ? 'selected' : '' ?>>Semua Kategori</option>
                                    <option value="pelajar" <?= $kategori_filter === 'pelajar' ? 'selected' : '' ?>>Umum/Siswa/Mahasiswa</option>
                                    <option value="pengajar" <?= $kategori_filter === 'pengajar' ? 'selected' : '' ?>>Pengajar</option>
                                </select>
                                <?php if (!empty($search)): ?>
                                    <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
                                <?php endif; ?>
                                <?php if ($current_page > 1): ?>
                                    <input type="hidden" name="page" value="<?= $current_page ?>">
                                <?php endif; ?>
                            </div>
                        </form>

                        <!-- Form Pengembalian -->
                        <form method="POST" class="form-row">
                            <div class="input-group">
                                <label for="id_peminjaman"><i class="fas fa-book"></i> Pilih Peminjaman:</label>
                                <select name="id_peminjaman" class="form-select" required>
                                    <option value="">-- Pilih Peminjaman --</option>
                                    <?php while ($row = mysqli_fetch_assoc($peminjaman_dropdown)) {
                                        echo "<option value='{$row['id_peminjaman']}'>
                                            {$row['id_peminjaman']} - {$row['username']} - {$row['judul']} (Jatuh Tempo: " . date('d-m-Y', strtotime($row['tanggal_kembali'])) . ")
                                        </option>";
                                    } ?>
                                </select>
                            </div>

                            <div class="input-group">
                                <label for="tanggal_pengembalian"><i class="fas fa-calendar"></i> Tanggal Pengembalian:</label>
                                <input type="date" name="tanggal_pengembalian" value="<?= date('Y-m-d') ?>" class="form-control" required>
                            </div>

                            <button type="submit" name="kembalikan" class="btn btn-primary">
                                <i class="fas fa-check"></i> Kembalikan Buku
                            </button>
                        </form>
                    </div>
        

                <!-- Form Pencarian -->
             
                    <div class="card-header">
                        <h3><i class="fas fa-search"></i> Pencarian Data Pengembalian</h3>
                                </div>
                    <div class="card-body">
                        <form method="GET" class="search-form">
                            <div class="search-container">
                                <input type="text" name="search" placeholder="Cari ID peminjaman, username, judul buku, kategori, atau status..." 
                                    value="<?= htmlspecialchars($search) ?>" class="input-search">
                                <?php if ($kategori_filter !== 'semua'): ?>
                                    <input type="hidden" name="kategori" value="<?= $kategori_filter ?>">
                                <?php endif; ?>
                            </div>
                            <div class="search-buttons">
                                <button type="submit" class="btn-search">
                                    <i class="fas fa-search"></i> Cari
                                </button>
                                <a href="<?= $kategori_filter !== 'semua' ? 'pengembalian.php?kategori=' . $kategori_filter : 'pengembalian.php' ?>" class="btn-reset">
                                    <i class="fas fa-times"></i> Reset
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Tabel Laporan -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h3><i class="fas fa-table"></i> Laporan Pengembalian & Denda</h3>
                        <?php if (!empty($search)): ?>
                            <div class="search-info">
                                <i class="fas fa-info-circle"></i>
                                Menampilkan hasil pencarian untuk: <strong>"<?= htmlspecialchars($search) ?>"</strong>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <?php if (mysqli_num_rows($laporan_query) > 0): ?>
                            <div class="table-responsive">
                                <table class="table data-table">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>ID Peminjaman</th>
                                            <th>Username</th>
                                            <th>Kategori</th>
                                            <th>Judul Buku</th>
                                            <th>Pengajuan</th>
                                            <th>Jatuh Tempo</th>
                                            <th>Pengembalian</th>
                                            <th>Status</th>
                                            <th>Telat</th>
                                            <th>Denda</th>
                                            <?php if ($kategori_filter === 'pengajar') echo "<th>Jumlah Buku</th>"; ?>
                                            <th>Status Lunas</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php 
                                    $no = $offset + 1; // Nomor urut berdasarkan halaman
                                    while ($row = mysqli_fetch_assoc($laporan_query)) {
                                        $telat = max(0, ceil((strtotime($row['tanggal_pengembalian']) - strtotime($row['tanggal_kembali'])) / 86400));
                                        $status_class = $telat > 0 ? 'status-merah' : 'status-hijau';
                                        $status_text = $telat > 0 ? 'Terlambat' : 'Tepat Waktu';
                                        $badge_class = $row['status_lunas'] === 'lunas' ? 'badge-lunas' : 'badge-belum-lunas';
                                    ?>
                                        <tr>
                                            <td class="text-center"><strong><?= $no++ ?></strong></td>
                                            <td class="text-center"><?= $row['id_peminjaman'] ?></td>
                                            <td><?= htmlspecialchars($row['username']) ?></td>
                                            <td>
                                                <span class="kategori-badge">
                                                    <?= htmlspecialchars($row['kategori']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="book-title">
                                                    <?= htmlspecialchars($row['judul']) ?>
                                                </div>
                                            </td>
                                            <td class="text-center"><?= date('d-m-Y', strtotime($row['tanggal_pengajuan'])) ?></td>
                                            <td class="text-center"><?= date('d-m-Y', strtotime($row['tanggal_kembali'])) ?></td>
                                            <td class="text-center"><?= date('d-m-Y', strtotime($row['tanggal_pengembalian'])) ?></td>
                                            <td class="text-center">
                                                <span class="badge <?= $status_class ?>"><?= $status_text ?></span>
                                            </td>
                                            <td class="text-center"><?= $telat ?> hari</td>
                                            <td class="text-center">
                                                <strong class="<?= $row['denda'] > 0 ? 'text-danger' : 'text-success' ?>">
                                                    Rp<?= number_format($row['denda'], 0, ',', '.') ?>
                                                </strong>
                                            </td>
                                            <?php if ($kategori_filter === 'pengajar') echo "<td class='text-center'>{$row['jumlah_buku']}</td>"; ?>
                                            <td class="text-center">
                                                <span class="badge <?= $badge_class ?>">
                                                    <?= $row['status_lunas'] === 'lunas' ? '✔ Lunas' : '✘ Belum Lunas' ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="aksi-buttons">
                                                    <?php if ($row['denda'] > 0 && $row['status_lunas'] !== 'lunas') { ?>
                                                        <a href="lunas.php?id=<?= $row['id_pengembalian'] ?>" 
                                                           class="button success"
                                                           onclick="return confirm('Sudah menerima pembayaran denda?')"
                                                           title="Tandai Lunas">
                                                            <i class="fas fa-check"></i> Lunas
                                                        </a>
                                                    <?php } ?>
                                                    <a href="hapus_pengembalian.php?id=<?= $row['id_pengembalian'] ?>" 
                                                       class="button delete"
                                                       onclick="return confirm('Hapus data pengembalian ini?\n\nData yang sudah dihapus tidak dapat dikembalikan!')"
                                                       title="Hapus Data">
                                                        <i class="fas fa-trash"></i> Hapus
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <?php if ($total_pages > 1): ?>
                                <div class="pagination-container">
                                    <div class="pagination-info">
                                        <i class="fas fa-info-circle"></i>
                                        Menampilkan <?= ($offset + 1) ?> - <?= min($offset + $records_per_page, $total_records) ?> 
                                        dari <?= $total_records ?> data (Halaman <?= $current_page ?> dari <?= $total_pages ?>)
                                    </div>
                                    
                                    <div class="pagination">
                                        <!-- Previous Button -->
                                        <?php if ($current_page > 1): ?>
                                            <a href="<?= build_url(1) ?>" class="pagination-btn">
                                                <i class="fas fa-angle-double-left"></i>
                                                <span class="btn-text">First</span>
                                            </a>
                                            <a href="<?= build_url($current_page - 1) ?>" class="pagination-btn prev">
                                                <i class="fas fa-angle-left"></i>
                                                <span class="btn-text">Previous</span>
                                            </a>
                                        <?php else: ?>
                                            <span class="pagination-btn disabled">
                                                <i class="fas fa-angle-double-left"></i>
                                                <span class="btn-text">First</span>
                                            </span>
                                            <span class="pagination-btn disabled prev">
                                                <i class="fas fa-angle-left"></i>
                                                <span class="btn-text">Previous</span>
                                            </span>
                                        <?php endif; ?>

                                        <!-- Page Numbers -->
                                        <?php
                                        $start_page = max(1, $current_page - 2);
                                        $end_page = min($total_pages, $current_page + 2);
                                        
                                        // Adjust if we're near the beginning or end
                                        if ($end_page - $start_page < 4) {
                                            if ($start_page == 1) {
                                                $end_page = min($total_pages, $start_page + 4);
                                            } else {
                                                $start_page = max(1, $end_page - 4);
                                            }
                                        }

                                        for ($i = $start_page; $i <= $end_page; $i++): ?>
                                            <?php if ($i == $current_page): ?>
                                                <span class="pagination-btn active page-num"><?= $i ?></span>
                                            <?php else: ?>
                                                <a href="<?= build_url($i) ?>" class="pagination-btn page-num"><?= $i ?></a>
                                            <?php endif; ?>
                                        <?php endfor; ?>

                                        <!-- Next Button -->
                                        <?php if ($current_page < $total_pages): ?>
                                            <a href="<?= build_url($current_page + 1) ?>" class="pagination-btn">
                                                <span class="btn-text">Next</span>
                                                <i class="fas fa-angle-right"></i>
                                            </a>
                                            <a href="<?= build_url($total_pages) ?>" class="pagination-btn">
                                                <span class="btn-text">Last</span>
                                                <i class="fas fa-angle-double-right"></i>
                                            </a>
                                        <?php else: ?>
                                            <span class="pagination-btn disabled">
                                                <span class="btn-text">Next</span>
                                                <i class="fas fa-angle-right"></i>
                                            </span>
                                            <span class="pagination-btn disabled">
                                                <span class="btn-text">Last</span>
                                                <i class="fas fa-angle-double-right"></i>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-search"></i>
                                <h3>
                                    <?php if (!empty($search)): ?>
                                        Tidak Ditemukan Hasil Pencarian
                                    <?php elseif ($kategori_filter !== 'semua'): ?>
                                        Tidak Ada Data Pengembalian di Kategori Ini
                                    <?php else: ?>
                                        Belum Ada Data Pengembalian
                                    <?php endif; ?>
                                </h3>
                                <p>
                                    <?php if (!empty($search)): ?>
                                        Tidak ada data pengembalian yang sesuai dengan kata kunci "<?= htmlspecialchars($search) ?>".
                                        <br>Coba gunakan kata kunci yang berbeda atau hapus filter pencarian.
                                    <?php elseif ($kategori_filter !== 'semua'): ?>
                                        Belum ada data pengembalian untuk kategori yang dipilih.
                                    <?php else: ?>
                                        Belum ada buku yang dikembalikan. Data akan muncul setelah ada proses pengembalian buku.
                                    <?php endif; ?>
                                </p>
                                <?php if (!empty($search)): ?>
                                    <a href="<?= $kategori_filter !== 'semua' ? 'pengembalian.php?kategori=' . $kategori_filter : 'pengembalian.php' ?>" class="button secondary">
                                        <i class="fas fa-times"></i> Hapus Pencarian
                                    </a>
                                <?php elseif ($kategori_filter !== 'semua'): ?>
                                    <a href="pengembalian.php" class="button secondary">
                                        <i class="fas fa-list"></i> Lihat Semua Data
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer>
        &copy; <?= date('Y') ?> Perpustakaan - Litera
    </footer>

    <script>
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

        // Focus on search input when page loads
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.querySelector('input[name="search"]');
            if (searchInput && searchInput.value === '') {
                searchInput.focus();
            }
        });

        // Smooth scroll to top when pagination is clicked
        document.querySelectorAll('.pagination-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                if (!this.classList.contains('disabled') && !this.classList.contains('active')) {
                    // Show loading state
                    this.style.opacity = '0.6';
                    this.style.pointerEvents = 'none';
                    
                    // Scroll to table
                    const table = document.querySelector('.data-table');
                    if (table) {
                        table.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                }
            });
        });

        // Add keyboard navigation for pagination
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey) {
                const currentPage = <?= $current_page ?>;
                const totalPages = <?= $total_pages ?>;
                
                // Ctrl + Left Arrow = Previous page
                if (e.key === 'ArrowLeft' && currentPage > 1) {
                    e.preventDefault();
                    window.location.href = '<?= addslashes(build_url($current_page - 1)) ?>';
                }
                
                // Ctrl + Right Arrow = Next page
                if (e.key === 'ArrowRight' && currentPage < totalPages) {
                    e.preventDefault();
                    window.location.href = '<?= addslashes(build_url($current_page + 1)) ?>';
                }
            }
        });
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