<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

include '../config/config.php';

// Filter kategori
$kategori_filter = $_GET['kategori'] ?? 'semua';

// Query untuk filter kategori
$where_clause = "";
if ($kategori_filter === 'umum') {
    $where_clause = "AND k.nama_tipe = 'umum'";
} elseif ($kategori_filter === 'pelajar') {
    $where_clause = "AND k.nama_tipe = 'pelajar'";
} elseif ($kategori_filter === 'pengajar') {
    $where_clause = "AND k.nama_tipe = 'pengajar'";
}

// Query utama pengajuan buku
$pengajuan_query = mysqli_query($conn, "
    SELECT 
        pb.id_pengajuan, pb.status, pb.tanggal_pengajuan, pb.is_deleted,
        u.username, u.nama, k.nama_tipe AS kategori,
        MAX(pj.tanggal_kembali) AS tanggal_pengembalian
    FROM pengajuan_buku pb
    JOIN users u ON pb.id_user = u.id_user
    JOIN keanggotaan k ON u.id_keanggotaan = k.id_keanggotaan
    LEFT JOIN peminjaman pj ON pb.id_pengajuan = pj.id_pengajuan
    WHERE pb.is_deleted = 0 $where_clause
    GROUP BY pb.id_pengajuan
    ORDER BY pb.tanggal_pengajuan DESC
");

// Hitung total pengajuan
$total_pengajuan = mysqli_num_rows($pengajuan_query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Pengajuan Buku - Litera</title>
    <link rel="stylesheet" href="../assets/css/admin1.css">
  <link rel="stylesheet" href="../assets/css/sidebar.css">
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
                    <i class="fas fa-book-reader"></i> Daftar Pengajuan Buku
                    <span class="total-count">(<?= $total_pengajuan ?> pengajuan)</span>
                </h2>
            </div>

            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert <?= isset($_SESSION['error']) ? 'alert-error' : 'alert-success' ?>">
                    <i class="fas <?= isset($_SESSION['error']) ? 'fa-exclamation-circle' : 'fa-check-circle' ?>"></i>
                    <?= $_SESSION['message'] ?>
                </div>
                <?php 
                unset($_SESSION['message']);
                unset($_SESSION['error']);
                ?>
            <?php endif; ?>

            <!-- Filter Kategori -->
            <div class="kategori-filter">
                <strong><i class="fas fa-filter"></i> Filter Kategori:</strong>
                <div class="kategori-list">
                    <a href="pengajuan_list.php" class="<?= $kategori_filter === 'semua' ? 'active' : '' ?>">
                        <i class="fas fa-list"></i> Semua
                    </a>
                    <a href="pengajuan_list.php?kategori=umum" class="<?= $kategori_filter === 'umum' ? 'active' : '' ?>">
                        <i class="fas fa-user"></i> Umum
                    </a>
                    <a href="pengajuan_list.php?kategori=pelajar" class="<?= $kategori_filter === 'pelajar' ? 'active' : '' ?>">
                        <i class="fas fa-user-graduate"></i> Pelajar
                    </a>
                    <a href="pengajuan_list.php?kategori=pengajar" class="<?= $kategori_filter === 'pengajar' ? 'active' : '' ?>">
                        <i class="fas fa-chalkboard-teacher"></i> Pengajar
                    </a>
                </div>
            </div>

            <!-- Tabel Pengajuan -->
            <div class="table-container">
                <?php if (mysqli_num_rows($pengajuan_query) > 0): ?>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th width="50">No</th>
                                    <th width="80">Gambar</th>
                                    <th>Judul Buku</th>
                                    <th>Pengaju</th>
                                    <th width="120">Kategori</th>
                                    <th width="80">Jumlah</th>
                                    <th width="120">Tgl. Pengajuan</th>
                                    <th width="120">Tgl. Kembali</th>
                                    <th width="100">Status</th>
                                    <th width="150">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; ?>
                                <?php mysqli_data_seek($pengajuan_query, 0); // Reset pointer ?>
                                <?php while ($row = mysqli_fetch_assoc($pengajuan_query)): 
                                    $id_pengajuan = $row['id_pengajuan'];
                                    $judul_buku = [];
                                    $total_buku = 0;
                                    $gambar = '../assets/images/default_book.png';

                                    // Query detail buku
                                    $buku_q = mysqli_query($conn, "
                                        SELECT b.judul, b.gambar, dp.jumlah FROM detail_pengajuan_buku dp
                                        JOIN buku b ON dp.id_buku = b.id_buku
                                        WHERE dp.id_pengajuan = '$id_pengajuan'
                                    ");
                                    
                                    while ($b = mysqli_fetch_assoc($buku_q)) {
                                        $judul_buku[] = htmlspecialchars($b['judul']);
                                        $total_buku += $b['jumlah'];
                                        if ($gambar === '../assets/images/default_book.png' && !empty($b['gambar'])) {
                                            $gambar = '../uploads/' . $b['gambar'];
                                        }
                                    }

                                    // Cek status pengembalian
                                    $cek = mysqli_query($conn, "
                                        SELECT k.status_lunas 
                                        FROM peminjaman p
                                        LEFT JOIN pengembalian k ON p.id_peminjaman = k.id_peminjaman
                                        WHERE p.id_pengajuan = '$id_pengajuan'
                                    ");
                                    
                                    $semua_kembali = true;
                                    $semua_lunas = true;
                                    
                                    while ($c = mysqli_fetch_assoc($cek)) {
                                        if (!$c) $semua_kembali = false;
                                        elseif (strtolower($c['status_lunas']) !== 'lunas') $semua_lunas = false;
                                    }

                                    $boleh_hapus = in_array($row['status'], ['disetujui', 'ditolak']) && $semua_kembali && $semua_lunas;
                                ?>
                                <tr>
                                    <td class="text-center"><strong><?= $no++ ?></strong></td>
                                    <td class="text-center">
                                        <?php if ($gambar !== '../assets/images/default_book.png'): ?>
                                            <img src="<?= $gambar ?>" 
                                                 alt="<?= htmlspecialchars(implode(', ', $judul_buku)) ?>"
                                                 class="book-thumbnail"
                                                 onclick="showImageModal('<?= $gambar ?>', '<?= htmlspecialchars(implode(', ', $judul_buku)) ?>')">
                                        <?php else: ?>
                                            <div class="no-image">
                                                <i class="fas fa-book"></i>
                                                <small>No Image</small>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="book-title">
                                            <strong><?= implode(', ', $judul_buku) ?></strong>
                                        </div>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($row['nama']) ?><br>
                                        <small class="text-muted">@<?= htmlspecialchars($row['username']) ?></small>
                                    </td>
                                    <td class="text-center">
                                        <span class="kategori-badge">
                                            <?= ucfirst($row['kategori']) ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge <?= $total_buku > 0 ? 'badge-success' : 'badge-danger' ?>">
                                            <?= $total_buku ?>
                                        </span>
                                    </td>
                                    <td class="text-center"><?= date('d-m-Y', strtotime($row['tanggal_pengajuan'])) ?></td>
                                    <td class="text-center">
                                        <?= $row['tanggal_pengembalian'] ? date('d-m-Y', strtotime($row['tanggal_pengembalian'])) : '-' ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge <?php 
                                            switch(strtolower($row['status'])) {
                                                case 'pending': echo 'badge-warning'; break;
                                                case 'disetujui': echo 'badge-success'; break;
                                                case 'ditolak': echo 'badge-danger'; break;
                                                default: echo 'badge-secondary';
                                            }
                                        ?>">
                                            <?= ucfirst($row['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="aksi-buttons">
                                            <?php if ($row['status'] === 'pending'): ?>
                                                <a href="verifikasi_pengajuan.php?id=<?= $id_pengajuan ?>&aksi=setujui" 
                                                   class="button success" 
                                                   title="Setujui Pengajuan">
                                                    <i class="fas fa-check"></i> Setujui
                                                </a>
                                                <a href="verifikasi_pengajuan.php?id=<?= $id_pengajuan ?>&aksi=tolak" 
                                                   class="button warning" 
                                                   title="Tolak Pengajuan">
                                                    <i class="fas fa-times"></i> Tolak
                                                </a>
                                            <?php elseif ($boleh_hapus): ?>
                                                <a href="hapus_pengajuan.php?id=<?= $id_pengajuan ?>" 
                                                   class="button delete" 
                                                   onclick="return confirm('Yakin ingin menghapus pengajuan ini?\n\nData yang sudah dihapus tidak dapat dikembalikan!');" 
                                                   title="Hapus Pengajuan">
                                                    <i class="fas fa-trash"></i> Hapus
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>

                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-book-open"></i>
                        <h3>
                            <?php if ($kategori_filter !== 'semua'): ?>
                                Tidak Ada Pengajuan untuk Kategori Ini
                            <?php else: ?>
                                Belum Ada Data Pengajuan
                            <?php endif; ?>
                        </h3>
                        <p>
                            <?php if ($kategori_filter !== 'semua'): ?>
                                Tidak ditemukan pengajuan buku untuk kategori yang dipilih.
                                <br>Coba pilih kategori lain atau lihat semua pengajuan.
                            <?php else: ?>
                                Saat ini belum ada pengajuan peminjaman buku dari anggota perpustakaan.
                            <?php endif; ?>
                        </p>
                        <?php if ($kategori_filter !== 'semua'): ?>
                            <a href="pengajuan_list.php" class="button secondary">
                                <i class="fas fa-list"></i> Lihat Semua Pengajuan
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div> 
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
    </script>

    <style>
        /* Additional CSS for responsive improvements */
        @media (max-width: 768px) {
            .table-responsive {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
            
            .data-table {
                min-width: 900px;
                font-size: 0.9rem;
            }
            
            .data-table th,
            .data-table td {
                padding: 8px 6px;
                white-space: nowrap;
            }
            
            .book-thumbnail {
                width: 40px;
                height: 50px;
                object-fit: cover;
                border-radius: 4px;
                cursor: pointer;
                transition: transform 0.2s ease;
            }
            
            .book-thumbnail:hover {
                transform: scale(1.1);
            }
            
            .no-image {
                width: 40px;
                height: 50px;
                background: #f5f5f5;
                border: 1px dashed #ddd;
                border-radius: 4px;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                font-size: 0.7rem;
                color: #999;
            }
            
            .no-image i {
                font-size: 16px;
                margin-bottom: 2px;
            }
            
            .aksi-buttons {
                display: flex;
                flex-direction: column;
                gap: 4px;
            }
            
            .aksi-buttons .button {
                font-size: 0.8rem;
                padding: 4px 8px;
                min-width: auto;
            }
            
            .badge {
                font-size: 0.8rem;
                padding: 2px 6px;
            }
            
            .kategori-badge {
                font-size: 0.8rem;
                padding: 2px 6px;
                background: #e3f2fd;
                color: #1976d2;
                border-radius: 12px;
                display: inline-block;
            }
            
            .book-title strong {
                font-size: 0.9rem;
                line-height: 1.3;
                display: block;
                max-width: 200px;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }
            
            .text-muted {
                color: #6c757d;
                font-size: 0.8rem;
            }
        }
        
        @media (max-width: 480px) {
            .data-table {
                font-size: 0.8rem;
            }
            
            .data-table th,
            .data-table td {
                padding: 6px 4px;
            }
            
            .book-thumbnail {
                width: 35px;
                height: 45px;
            }
            
            .no-image {
                width: 35px;
                height: 45px;
            }
            
            .aksi-buttons .button {
                font-size: 0.7rem;
                padding: 3px 6px;
            }
            
            .book-title strong {
                max-width: 150px;
                font-size: 0.8rem;
            }
        }
    </style>
    <script>
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