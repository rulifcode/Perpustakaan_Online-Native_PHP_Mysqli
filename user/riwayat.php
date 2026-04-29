<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}
include '../config/config.php';

$id_user = $_SESSION['user_id'];

$get_kategori = mysqli_query($conn, "SELECT kategori FROM users WHERE id_user = '$id_user'");
$kategori_user = mysqli_fetch_assoc($get_kategori)['kategori'];

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Fungsi untuk menangani hapus pengajuan
if (isset($_POST['hapus_pengajuan'])) {
    $id_pengajuan = mysqli_real_escape_string($conn, $_POST['id_pengajuan']);

    // Pastikan pengajuan milik user yang sedang login
    $check_query = mysqli_query($conn, "
        SELECT 
            pb.status,
            -- Cek status pengembalian dan pelunasan
            (
                SELECT 
                    CASE 
                        WHEN COUNT(pg2.id_pengembalian) > 0 THEN 'sudah_dikembalikan'
                        ELSE 'belum_dikembalikan'
                    END
                FROM peminjaman p2
                LEFT JOIN pengembalian pg2 ON p2.id_peminjaman = pg2.id_peminjaman
                WHERE p2.id_pengajuan = pb.id_pengajuan
            ) as status_pengembalian,
            
            -- Status lunas
            (
                SELECT 
                    CASE 
                        WHEN COUNT(pg2.id_pengembalian) > 0 AND
                             COUNT(CASE WHEN LOWER(TRIM(pg2.status_lunas)) = 'lunas' THEN 1 END) = COUNT(pg2.id_pengembalian)
                        THEN 'lunas'
                        ELSE 'belum_lunas'
                    END
                FROM peminjaman p2
                JOIN pengembalian pg2 ON p2.id_peminjaman = pg2.id_peminjaman
                WHERE p2.id_pengajuan = pb.id_pengajuan
            ) as status_lunas
            
        FROM pengajuan_buku pb
        WHERE pb.id_pengajuan = '$id_pengajuan' AND pb.id_user = '$id_user'
    ");

    if ($check_data = mysqli_fetch_assoc($check_query)) {
        $status = strtolower(trim($check_data['status']));
        $status_pengembalian = $check_data['status_pengembalian'];
        $status_lunas = $check_data['status_lunas'];

        // Kondisi untuk mengizinkan hapus:
        // 1. Status pending/menunggu atau ditolak (seperti sebelumnya)
        // 2. Status disetujui + sudah dikembalikan + sudah lunas
        $boleh_hapus = false;
        $alasan_tidak_boleh = '';

        if (in_array($status, ['pending', 'menunggu', 'ditolak'])) {
            $boleh_hapus = true;
        } elseif ($status == 'disetujui') {
            if ($status_pengembalian == 'sudah_dikembalikan' && $status_lunas == 'lunas') {
                $boleh_hapus = true;
            } elseif ($status_pengembalian == 'belum_dikembalikan') {
                $alasan_tidak_boleh = 'Buku belum dikembalikan';
            } elseif ($status_lunas == 'belum_lunas') {
                $alasan_tidak_boleh = 'Denda belum dilunasi';
            }
        }

        if ($boleh_hapus) {
            // Mulai transaction
            mysqli_autocommit($conn, FALSE);

            try {
                // Hapus data pengembalian terlebih dahulu (jika ada)
                $delete_pengembalian = mysqli_query($conn, "
                    DELETE pg FROM pengembalian pg
                    JOIN peminjaman p ON pg.id_peminjaman = p.id_peminjaman
                    WHERE p.id_pengajuan = '$id_pengajuan'
                ");

                // Hapus data peminjaman (jika ada)
                $delete_peminjaman = mysqli_query($conn, "
                    DELETE FROM peminjaman 
                    WHERE id_pengajuan = '$id_pengajuan'
                ");

                // Hapus detail pengajuan
                $delete_detail = mysqli_query($conn, "
                    DELETE FROM detail_pengajuan_buku 
                    WHERE id_pengajuan = '$id_pengajuan'
                ");

                if (!$delete_detail) {
                    throw new Exception("Gagal menghapus detail pengajuan");
                }

                // Hapus pengajuan
                $delete_pengajuan = mysqli_query($conn, "
                    DELETE FROM pengajuan_buku 
                    WHERE id_pengajuan = '$id_pengajuan'
                ");

                if (!$delete_pengajuan) {
                    throw new Exception("Gagal menghapus pengajuan");
                }

                // Commit transaction
                mysqli_commit($conn);

                echo "<script>
                    alert('Riwayat pengajuan berhasil dihapus!');
                    window.location.href = window.location.pathname;
                </script>";
            } catch (Exception $e) {
                // Rollback jika ada error
                mysqli_rollback($conn);
                echo "<script>alert('Gagal menghapus riwayat: - riwayat.php:127" . $e->getMessage() . "');</script>";
            }

            // Kembalikan autocommit
            mysqli_autocommit($conn, TRUE);
        } else {
            if ($alasan_tidak_boleh) {
                echo "<script>alert('Tidak dapat menghapus riwayat: $alasan_tidak_boleh');</script> - riwayat.php:134";
            } else {
                echo "<script>alert('Hanya pengajuan yang berstatus menunggu, ditolak, atau sudah selesai (dikembalikan & lunas) yang dapat dihapus!');</script> - riwayat.php:136";
            }
        }
    } else {
        echo "<script>alert('Pengajuan tidak ditemukan!');</script> - riwayat.php:140";
    }
}

// Query untuk menampilkan semua pengajuan (pending, disetujui, ditolak)
$query = mysqli_query($conn, "
    SELECT 
        pb.id_pengajuan,
        pb.tanggal_pengajuan,
        pb.status as status_pengajuan,
        GROUP_CONCAT(DISTINCT CONCAT(b.judul, ' (', dpb.jumlah, ')') SEPARATOR '<br>') as daftar_buku,
        COUNT(DISTINCT dpb.id_buku) as jumlah_jenis_buku,
        SUM(dpb.jumlah) as total_buku,
        
        -- Data peminjaman (jika disetujui)
        (
            SELECT MIN(p2.tanggal_pinjam)
            FROM peminjaman p2
            WHERE p2.id_pengajuan = pb.id_pengajuan
        ) as tanggal_pinjam,

        (
            SELECT MIN(p2.tanggal_kembali)
            FROM peminjaman p2
            WHERE p2.id_pengajuan = pb.id_pengajuan
        ) as tanggal_kembali,

        (
            SELECT GROUP_CONCAT(p2.id_peminjaman)
            FROM peminjaman p2
            WHERE p2.id_pengajuan = pb.id_pengajuan
        ) as id_peminjaman_list,

        -- Cek status pengembalian
        (
            SELECT 
                CASE 
                    WHEN COUNT(pg2.id_pengembalian) > 0 THEN 'sudah_dikembalikan'
                    ELSE 'belum_dikembalikan'
                END
            FROM peminjaman p2
            LEFT JOIN pengembalian pg2 ON p2.id_peminjaman = pg2.id_peminjaman
            WHERE p2.id_pengajuan = pb.id_pengajuan
        ) as status_pengembalian,

        -- Ambil total denda dari database (tanpa hitung ulang)
        (
            SELECT COALESCE(SUM(pg2.denda), 0)
            FROM peminjaman p2
            JOIN pengembalian pg2 ON p2.id_peminjaman = pg2.id_peminjaman
            WHERE p2.id_pengajuan = pb.id_pengajuan
        ) as total_denda,

        -- Status lunas (jika semua pengembalian statusnya 'lunas')
        (
            SELECT 
                CASE 
                    WHEN COUNT(pg2.id_pengembalian) > 0 AND
                         COUNT(CASE WHEN LOWER(TRIM(pg2.status_lunas)) = 'lunas' THEN 1 END) = COUNT(pg2.id_pengembalian)
                    THEN 'lunas'
                    ELSE 'belum_lunas'
                END
            FROM peminjaman p2
            JOIN pengembalian pg2 ON p2.id_peminjaman = pg2.id_peminjaman
            WHERE p2.id_pengajuan = pb.id_pengajuan
        ) as status_lunas,

        (
            SELECT MAX(pg2.tanggal_pengembalian)
            FROM peminjaman p2
            JOIN pengembalian pg2 ON p2.id_peminjaman = pg2.id_peminjaman
            WHERE p2.id_pengajuan = pb.id_pengajuan
        ) as tanggal_pengembalian

    FROM pengajuan_buku pb
    JOIN detail_pengajuan_buku dpb ON pb.id_pengajuan = dpb.id_pengajuan
    JOIN buku b ON dpb.id_buku = b.id_buku
    WHERE pb.id_user = '$id_user'
    GROUP BY pb.id_pengajuan, pb.tanggal_pengajuan, pb.status
    ORDER BY pb.tanggal_pengajuan DESC
    LIMIT $limit OFFSET $offset
");

// Query count
$count_query = mysqli_query($conn, "
    SELECT COUNT(DISTINCT pb.id_pengajuan) as total 
    FROM pengajuan_buku pb
    WHERE pb.id_user = '$id_user'
");

$total_data = mysqli_fetch_assoc($count_query)['total'];
$total_pages = ceil($total_data / $limit);

// Kategorisasi data
$data_semua = [];
$data_menunggu = [];
$data_disetujui = [];
$data_ditolak = [];

$stats = [
    'total_pengajuan' => 0,
    'menunggu' => 0,
    'disetujui' => 0,
    'ditolak' => 0,
    'belum_kembali' => 0,
    'sudah_kembali' => 0,
    'total_denda' => 0,
    'denda_belum_lunas' => 0
];

// Function to check if record can be deleted
function canDelete($row)
{
    $status = strtolower(trim($row['status_pengajuan']));

    // Always allow deletion for pending/rejected
    if (in_array($status, ['pending', 'menunggu', 'ditolak'])) {
        return true;
    }

    // For approved, allow deletion only if returned and paid
    if ($status == 'disetujui') {
        return ($row['status_pengembalian'] == 'sudah_dikembalikan' &&
            $row['status_lunas'] == 'lunas');
    }

    return false;
}

while ($row = mysqli_fetch_assoc($query)) {
    // Add delete permission flag
    $row['can_delete'] = canDelete($row);

    $data_semua[] = $row;
    $stats['total_pengajuan']++;

    // Normalize status untuk konsistensi
    $status_normalized = strtolower(trim($row['status_pengajuan']));

    // Kategorisasi berdasarkan status pengajuan
    switch ($status_normalized) {
        case 'pending':
        case 'menunggu':
            $data_menunggu[] = $row;
            $stats['menunggu']++;
            break;
        case 'disetujui':
        case 'approved':
            $data_disetujui[] = $row;
            $stats['disetujui']++;

            // Hitung statistik pengembalian hanya untuk yang disetujui
            if ($row['status_pengembalian'] == 'belum_dikembalikan') {
                $stats['belum_kembali']++;
            } else if ($row['status_pengembalian'] == 'sudah_dikembalikan') {
                $stats['sudah_kembali']++;

                // Tambahkan denda dari database (bukan kalkulasi ulang)
                $stats['total_denda'] += $row['total_denda'];

                // Jika status belum lunas, masukkan ke denda belum lunas
                if ($row['status_lunas'] == 'belum_lunas') {
                    $stats['denda_belum_lunas'] += $row['total_denda'];
                }
            }
            break;
        case 'ditolak':
        case 'rejected':
            $data_ditolak[] = $row;
            $stats['ditolak']++;
            break;
    }
}

?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Peminjaman Buku - Litera</title>
    <link rel="icon" type="image/png" href="../assets/img/favicon-32x32.png" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/riwayat_user.css">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="../assets/css/header.css">
    <link rel="stylesheet" href="../assets/css/footer.css">
    <script src="../assets/js/header.js"></script>
    <script src="../assets/js/animasi.js" defer></script>
    <script src="../assets/js/scroll.js" defer></script>
</head>

<body>
    <?php include '../partials/header_user.php'; ?>

    <section class="riwayat-section">
        <div class="container">
            <h2>Riwayat Pengajuan & Peminjaman Buku</h2>

            <!-- Statistics Cards sebagai Tab Navigation -->
            <div class="stats-container">
                <div class="stat-card tab" data-tab="semua" style="cursor: pointer;">
                    <div class="stat-number"><?= $stats['total_pengajuan'] ?></div>
                    <div class="stat-label">Semua (<?= $stats['total_pengajuan'] ?>)</div>
                </div>
                <div class="stat-card warning tab" data-tab="menunggu" style="cursor: pointer;">
                    <div class="stat-number"><?= $stats['menunggu'] ?></div>
                    <div class="stat-label">Menunggu (<?= $stats['menunggu'] ?>)</div>
                </div>
                <div class="stat-card success tab" data-tab="disetujui" style="cursor: pointer;">
                    <div class="stat-number"><?= $stats['disetujui'] ?></div>
                    <div class="stat-label">Disetujui (<?= $stats['disetujui'] ?>)</div>
                </div>
                <div class="stat-card danger tab" data-tab="ditolak" style="cursor: pointer;">
                    <div class="stat-number"><?= $stats['ditolak'] ?></div>
                    <div class="stat-label">Ditolak (<?= $stats['ditolak'] ?>)</div>
                </div>
            </div>

            <!-- Sub Statistics untuk yang disetujui -->
            <?php if ($stats['disetujui'] > 0): ?>
                <div class="stats-container" style="margin-top: 20px;">
                    <div class="stat-card success">
                        <div class="stat-number"><?= $stats['sudah_kembali'] ?></div>
                        <div class="stat-label">Sudah Dikembalikan</div>
                    </div>
                    <div class="stat-card danger">
                        <div class="stat-number"><?= $stats['belum_kembali'] ?></div>
                        <div class="stat-label">Belum Dikembalikan</div>
                    </div>
                    <div class="stat-card warning">
                        <div class="stat-number">Rp<?= number_format($stats['total_denda'] + $stats['denda_belum_lunas'], 0, ',', '.') ?></div>
                        <div class="stat-label">Total Denda</div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Alert untuk denda -->
            <?php if ($stats['denda_belum_lunas'] > 0): ?>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <div>
                        <strong>Perhatian!</strong> Anda memiliki potensi denda sebesar Rp<?= number_format($stats['denda_belum_lunas'], 0, ',', '.') ?> karena keterlambatan pengembalian.
                    </div>
                </div>
            <?php endif; ?>

            <!-- Tabs Container -->
            <div class="tabs-container">
                <!-- Tabs Navigation -->
                <div class="tabs">
                    <button class="tab active" onclick="openTab(event, 'semua-pengajuan')">
                        <i class="fas fa-list"></i>
                        <span>Semua (<?= count($data_semua) ?>)</span>
                    </button>
                    <button class="tab" onclick="openTab(event, 'menunggu')">
                        <i class="fas fa-clock"></i>
                        <span>Menunggu (<?= count($data_menunggu) ?>)</span>
                    </button>
                    <button class="tab" onclick="openTab(event, 'disetujui')">
                        <i class="fas fa-check-circle"></i>
                        <span>Disetujui (<?= count($data_disetujui) ?>)</span>
                    </button>
                    <button class="tab" onclick="openTab(event, 'ditolak')">
                        <i class="fas fa-times-circle"></i>
                        <span>Ditolak (<?= count($data_ditolak) ?>)</span>
                    </button>
                </div>

                <!-- Tab 1: Semua Pengajuan -->
                <div id="semua-pengajuan" class="tabcontent active">
                    <h3>Semua Riwayat Pengajuan</h3>

                    <!-- Desktop Table View -->
                    <div class="table-responsive desktop-view">
                        <table>
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal Pengajuan</th>
                                    <th>Daftar Buku</th>
                                    <th>Total Buku</th>
                                    <th>Status</th>
                                    <th>Tanggal Pinjam</th>
                                    <th>Status Pengembalian</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($data_semua)): ?>
                                    <tr>
                                        <td colspan="8" class="no-data">Belum ada pengajuan</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($data_semua as $index => $row): ?>
                                        <tr>
                                            <td><?= $offset + $index + 1 ?></td>
                                            <td><?= date('d/m/Y H:i', strtotime($row['tanggal_pengajuan'])) ?></td>
                                            <td class="daftar-buku"><?= $row['daftar_buku'] ?></td>
                                            <td><strong><?= $row['total_buku'] ?></strong></td>
                                            <td>
                                                <?php
                                                switch ($row['status_pengajuan']) {
                                                    case 'menunggu':
                                                        echo '<span class="badgemenunggu">Menunggu</span> - riwayat.php:448';
                                                        break;
                                                    case 'disetujui':
                                                        echo '<span class="badgedisetujui">Disetujui</span> - riwayat.php:451';
                                                        if ($row['status_pengembalian'] == 'sudah_dikembalikan') {
                                                            echo '<br><span class="badgelunas">Selesai</span> - riwayat.php:453';
                                                        }
                                                        break;
                                                    case 'ditolak':
                                                        echo '<span class="badgeditolak">Ditolak</span> - riwayat.php:457';
                                                        break;
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <?= $row['tanggal_pinjam'] ? date('d/m/Y', strtotime($row['tanggal_pinjam'])) : '-' ?>
                                            </td>
                                            <td>
                                                <?php if ($row['status_pengajuan'] == 'disetujui'): ?>
                                                    <?php if ($row['status_pengembalian'] == 'sudah_dikembalikan'): ?>
                                                        <span class="badge-lunas">Sudah Kembali</span>
                                                        <?php if ($row['status_lunas'] == 'lunas'): ?>
                                                            <br><span class="badge-lunas">Lunas</span>
                                                        <?php else: ?>
                                                            <br><span class="badge-belum-lunas">Belum Lunas</span>
                                                        <?php endif; ?>
                                                    <?php else: ?>
                                                        <span class="badge-belum-kembali">Belum Kembali</span>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    -
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="action-buttons">
                                                    <?php if ($row['can_delete']): ?>
                                                        <div class="tooltip">
                                                            <form method="post" onsubmit="return confirm('Apakah Anda yakin ingin menghapus riwayat ini? Data yang dihapus tidak dapat dikembalikan.')" style="display:inline;">
                                                                <input type="hidden" name="id_pengajuan" value="<?= $row['id_pengajuan'] ?>">
                                                                <?php if ($row['status_pengajuan'] == 'disetujui' && $row['status_pengembalian'] == 'sudah_dikembalikan' && $row['status_lunas'] == 'lunas'): ?>
                                                                    <button type="submit" name="hapus_pengajuan" class="btn-hapus-selesai">
                                                                        <i class="fas fa-archive"></i> Arsipkan
                                                                    </button>
                                                                    <span class="tooltiptext">Hapus riwayat yang sudah selesai (dikembalikan & lunas)</span>
                                                                <?php else: ?>
                                                                    <button type="submit" name="hapus_pengajuan" class="btn-hapus-pengajuan">
                                                                        <i class="fas fa-trash"></i> Hapus
                                                                    </button>
                                                                    <span class="tooltiptext">Hapus pengajuan yang pending atau ditolak</span>
                                                                <?php endif; ?>
                                                            </form>
                                                        </div>
                                                    <?php else: ?>
                                                        <div class="tooltip">
                                                            <button class="btn-hapus-pengajuan" disabled>
                                                                <i class="fas fa-lock"></i> Terkunci
                                                            </button>
                                                            <span class="tooltiptext">
                                                                <?php
                                                                if ($row['status_pengajuan'] == 'disetujui') {
                                                                    if ($row['status_pengembalian'] == 'belum_dikembalikan') {
                                                                        echo 'Buku belum dikembalikan - riwayat.php:509';
                                                                    } elseif ($row['status_lunas'] == 'belum_lunas') {
                                                                        echo 'Denda belum dilunasi - riwayat.php:511';
                                                                    }
                                                                } else {
                                                                    echo 'Status tidak memungkinkan untuk dihapus - riwayat.php:514';
                                                                }
                                                                ?>
                                                            </span>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Cards View -->
                    <div class="cards-container mobile-view">
                        <?php if (empty($data_semua)): ?>
                            <div class="no-data">Belum ada pengajuan</div>
                        <?php else: ?>
                            <?php foreach ($data_semua as $index => $row): ?>
                                <div class="data-card status-<?= $row['status_pengajuan'] ?>">
                                    <div class="card-header">
                                        <div class="card-number"><?= $offset + $index + 1 ?></div>
                                        <div class="card-status">
                                            <?php
                                            switch ($row['status_pengajuan']) {
                                                case 'menunggu':
                                                    echo '<span class="badge badgemenunggu">Menunggu</span> - riwayat.php:542';
                                                    break;
                                                case 'disetujui':
                                                    echo '<span class="badge badgedisetujui">Disetujui</span> - riwayat.php:545';
                                                    if ($row['status_pengembalian'] == 'sudah_dikembalikan') {
                                                        echo '<br><span class="badge badgelunas">Selesai</span> - riwayat.php:547';
                                                    }
                                                    break;
                                                case 'ditolak':
                                                    echo '<span class="badge badgeditolak">Ditolak</span> - riwayat.php:551';
                                                    break;
                                            }
                                            ?>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="card-row">
                                            <div class="card-label">Tanggal</div>
                                            <div class="card-value"><?= date('d/m/Y H:i', strtotime($row['tanggal_pengajuan'])) ?></div>
                                        </div>
                                        <div class="card-row">
                                            <div class="card-label">Buku</div>
                                            <div class="card-value books-list"><?= $row['daftar_buku'] ?></div>
                                        </div>
                                        <div class="card-row">
                                            <div class="card-label">Total</div>
                                            <div class="card-value"><strong><?= $row['total_buku'] ?></strong></div>
                                        </div>
                                        <?php if ($row['tanggal_pinjam']): ?>
                                            <div class="card-row">
                                                <div class="card-label">Tgl Pinjam</div>
                                                <div class="card-value"><?= date('d/m/Y', strtotime($row['tanggal_pinjam'])) ?></div>
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($row['status_pengajuan'] == 'disetujui'): ?>
                                            <div class="card-row">
                                                <div class="card-label">Pengembalian</div>
                                                <div class="card-value">
                                                    <?php if ($row['status_pengembalian'] == 'sudah_dikembalikan'): ?>
                                                        <span class="badge badge-lunas">Sudah Kembali</span>
                                                        <?php if ($row['status_lunas'] == 'lunas'): ?>
                                                            <br><span class="badge badge-lunas">Lunas</span>
                                                        <?php else: ?>
                                                            <br><span class="badge badge-belum-lunas">Belum Lunas</span>
                                                        <?php endif; ?>
                                                    <?php else: ?>
                                                        <span class="badge badge-belum-kembali">Belum Kembali</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="card-actions">
                                        <?php if ($row['can_delete']): ?>
                                            <form method="post" onsubmit="return confirm('Apakah Anda yakin ingin menghapus riwayat ini? Data yang dihapus tidak dapat dikembalikan.')" style="display:inline;">
                                                <input type="hidden" name="id_pengajuan" value="<?= $row['id_pengajuan'] ?>">
                                                <?php if ($row['status_pengajuan'] == 'disetujui' && $row['status_pengembalian'] == 'sudah_dikembalikan' && $row['status_lunas'] == 'lunas'): ?>
                                                    <button type="submit" name="hapus_pengajuan" class="btn btn-archive">
                                                        <i class="fas fa-archive"></i> Arsipkan
                                                    </button>
                                                <?php else: ?>
                                                    <button type="submit" name="hapus_pengajuan" class="btn btn-hapus">
                                                        <i class="fas fa-trash"></i> Hapus
                                                    </button>
                                                <?php endif; ?>
                                            </form>
                                        <?php else: ?>
                                            <button class="btn btn-hapus" disabled>
                                                <i class="fas fa-lock"></i> Terkunci
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <div class="pagination">
                            <?php if ($page > 1): ?>
                                <a href="?page=<?= $page - 1 ?>">« Sebelumnya</a>
                            <?php endif; ?>

                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <?php if ($i == $page): ?>
                                    <span class="current"><?= $i ?></span>
                                <?php else: ?>
                                    <a href="?page=<?= $i ?>"><?= $i ?></a>
                                <?php endif; ?>
                            <?php endfor; ?>

                            <?php if ($page < $total_pages): ?>
                                <a href="?page=<?= $page + 1 ?>">Selanjutnya »</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Tab 2: Menunggu Persetujuan -->
                <div id="menunggu" class="tabcontent">
                    <h3>Pengajuan Menunggu Persetujuan</h3>

                    <!-- Desktop Table View -->
                    <div class="table-responsive desktop-view">
                        <table>
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal Pengajuan</th>
                                    <th>Daftar Buku</th>
                                    <th>Total Buku</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($data_menunggu)): ?>
                                    <tr>
                                        <td colspan="6" class="no-data">Tidak ada pengajuan yang menunggu persetujuan</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($data_menunggu as $index => $row): ?>
                                        <tr>
                                            <td><?= $index + 1 ?></td>
                                            <td><?= date('d/m/Y H:i', strtotime($row['tanggal_pengajuan'])) ?></td>
                                            <td class="daftar-buku"><?= $row['daftar_buku'] ?></td>
                                            <td><strong><?= $row['total_buku'] ?></strong></td>
                                            <td><span class="badge-menunggu">Menunggu</span></td>
                                            <td>
                                                <form method="post" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pengajuan ini?')" style="display:inline;">
                                                    <input type="hidden" name="id_pengajuan" value="<?= $row['id_pengajuan'] ?>">
                                                    <button type="submit" name="hapus_pengajuan" class="btn-hapus-pengajuan">
                                                        <i class="fas fa-trash"></i> Hapus
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Cards View -->
                    <div class="cards-container mobile-view">
                        <?php if (empty($data_menunggu)): ?>
                            <div class="no-data">Tidak ada pengajuan yang menunggu persetujuan</div>
                        <?php else: ?>
                            <?php foreach ($data_menunggu as $index => $row): ?>
                                <div class="data-card status-menunggu">
                                    <div class="card-header">
                                        <div class="card-number"><?= $index + 1 ?></div>
                                        <div class="card-status">
                                            <span class="badge badge-menunggu">Menunggu</span>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="card-row">
                                            <div class="card-label">Tanggal</div>
                                            <div class="card-value"><?= date('d/m/Y H:i', strtotime($row['tanggal_pengajuan'])) ?></div>
                                        </div>
                                        <div class="card-row">
                                            <div class="card-label">Buku</div>
                                            <div class="card-value books-list"><?= $row['daftar_buku'] ?></div>
                                        </div>
                                        <div class="card-row">
                                            <div class="card-label">Total</div>
                                            <div class="card-value"><strong><?= $row['total_buku'] ?></strong></div>
                                        </div>
                                    </div>
                                    <div class="card-actions">
                                        <form method="post" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pengajuan ini?')" style="display:inline;">
                                            <input type="hidden" name="id_pengajuan" value="<?= $row['id_pengajuan'] ?>">
                                            <button type="submit" name="hapus_pengajuan" class="btn btn-hapus">
                                                <i class="fas fa-trash"></i> Hapus
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>


                <!-- Tab 3: Disetujui -->
                <div id="disetujui" class="tabcontent">
                    <h3>Pengajuan yang Disetujui</h3>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal Pengajuan</th>
                                    <th>Daftar Buku</th>
                                    <th>Total Buku</th>
                                    <th>Tanggal Pinjam</th>
                                    <th>Tanggal Kembali</th>
                                    <th>Status Pengembalian</th>
                                    <th>Denda</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($data_disetujui)): ?>
                                    <tr>
                                        <td colspan="9" class="no-data">Tidak ada pengajuan yang disetujui</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($data_disetujui as $index => $row): ?>
                                        <tr>
                                            <td><?= $index + 1 ?></td>
                                            <td><?= date('d/m/Y H:i', strtotime($row['tanggal_pengajuan'])) ?></td>
                                            <td class="daftar-buku"><?= $row['daftar_buku'] ?></td>
                                            <td><strong><?= $row['total_buku'] ?></strong></td>
                                            <td><?= $row['tanggal_pinjam'] ? date('d/m/Y', strtotime($row['tanggal_pinjam'])) : '-' ?></td>
                                            <td><?= $row['tanggal_kembali'] ? date('d/m/Y', strtotime($row['tanggal_kembali'])) : '-' ?></td>
                                            <td>
                                                <?php if ($row['status_pengembalian'] == 'sudah_dikembalikan'): ?>
                                                    <span class="badge-lunas">Sudah Kembali</span>
                                                    <br><small><?= $row['tanggal_pengembalian'] ? date('d/m/Y', strtotime($row['tanggal_pengembalian'])) : '' ?></small>
                                                    <?php if ($row['status_lunas'] == 'lunas'): ?>
                                                        <br><span class="badge-lunas">Lunas</span>
                                                    <?php else: ?>
                                                        <br><span class="badge-belum-lunas">Belum Lunas</span>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="badge-belum-kembali">Belum Kembali</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($row['total_denda'] > 0): ?>
                                                    <strong>Rp<?= number_format($row['total_denda'], 0, ',', '.') ?></strong>
                                                <?php else: ?>
                                                    -
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($row['can_delete']): ?>
                                                    <form method="post" onsubmit="return confirm('Apakah Anda yakin ingin mengarsipkan riwayat ini?')" style="display:inline;">
                                                        <input type="hidden" name="id_pengajuan" value="<?= $row['id_pengajuan'] ?>">
                                                        <button type="submit" name="hapus_pengajuan" class="btn-hapus-selesai">
                                                            <i class="fas fa-archive"></i> Arsipkan
                                                        </button>
                                                    </form>
                                                <?php else: ?>
                                                    <span class="badge-belum-kembali">
                                                        <?php
                                                        if ($row['status_pengembalian'] == 'belum_dikembalikan') {
                                                            echo 'Aktif - riwayat.php:792';
                                                        } elseif ($row['status_lunas'] == 'belum_lunas') {
                                                            echo 'Belum Lunas - riwayat.php:794';
                                                        }
                                                        ?>
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <!-- Mobile Cards View -->
                    <div class="cards-container mobile-view">
                        <?php if (empty($data_disetujui)): ?>
                            <div class="no-data">Tidak ada pengajuan yang disetujui</div>
                        <?php else: ?>
                            <?php foreach ($data_disetujui as $index => $row): ?>
                                <div class="data-card status-disetujui">
                                    <div class="card-header">
                                        <div class="card-number"><?= $index + 1 ?></div>
                                        <div class="card-status">
                                            <?php if ($row['status_pengembalian'] == 'sudah_dikembalikan'): ?>
                                                <span class="badge-lunas">Sudah Kembali</span>
                                            <?php else: ?>
                                                <span class="badge-belum-kembali">Belum Kembali</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="card-row">
                                            <div class="card-label">Tanggal</div>
                                            <div class="card-value"><?= date('d/m/Y H:i', strtotime($row['tanggal_pengajuan'])) ?></div>
                                        </div>
                                        <div class="card-row">
                                            <div class="card-label">Buku</div>
                                            <div class="card-value"><?= $row['daftar_buku'] ?></div>
                                        </div>
                                        <div class="card-row">
                                            <div class="card-label">Total</div>
                                            <div class="card-value"><strong><?= $row['total_buku'] ?></strong></div>
                                        </div>
                                        <div class="card-row">
                                            <div class="card-label">Pinjam</div>
                                            <div class="card-value"><?= $row['tanggal_pinjam'] ? date('d/m/Y', strtotime($row['tanggal_pinjam'])) : '-' ?></div>
                                        </div>
                                        <div class="card-row">
                                            <div class="card-label">Kembali</div>
                                            <div class="card-value"><?= $row['tanggal_kembali'] ? date('d/m/Y', strtotime($row['tanggal_kembali'])) : '-' ?></div>
                                        </div>
                                        <div class="card-row">
                                            <div class="card-label">Denda</div>
                                            <div class="card-value">
                                                <?= $row['total_denda'] > 0 ? '<strong>Rp' . number_format($row['total_denda'], 0, ',', '.') . '</strong>' : '-' ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-actions">
                                        <?php if ($row['can_delete']): ?>
                                            <form method="post" onsubmit="return confirm('Apakah Anda yakin ingin mengarsipkan riwayat ini?')" style="display:inline;">
                                                <input type="hidden" name="id_pengajuan" value="<?= $row['id_pengajuan'] ?>">
                                                <button type="submit" name="hapus_pengajuan" class="btn btn-hapus">
                                                    <i class="fas fa-archive"></i> Arsipkan
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <span class="badge-belum-kembali">
                                                <?php
                                                if ($row['status_pengembalian - riwayat.php:862'] == 'belum_dikembalikan') echo 'Aktif';
                                                elseif ($row['status_lunas - riwayat.php:863'] == 'belum_lunas') echo 'Belum Lunas';
                                                ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                </div>


                <!-- Tab 4: Ditolak -->
                <div id="ditolak" class="tabcontent">
                    <h3>Pengajuan yang Ditolak</h3>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal Pengajuan</th>
                                    <th>Daftar Buku</th>
                                    <th>Total Buku</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($data_ditolak)): ?>
                                    <tr>
                                        <td colspan="6" class="no-data">Tidak ada pengajuan yang ditolak</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($data_ditolak as $index => $row): ?>
                                        <tr>
                                            <td><?= $index + 1 ?></td>
                                            <td><?= date('d/m/Y H:i', strtotime($row['tanggal_pengajuan'])) ?></td>
                                            <td class="daftar-buku"><?= $row['daftar_buku'] ?></td>
                                            <td><strong><?= $row['total_buku'] ?></strong></td>
                                            <td>
                                                <span class="badge-ditolak">Ditolak</span>
                                                <!-- Tampilkan keterangan penolakan jika ada -->
                                                <?php
                                                // Query untuk mendapatkan keterangan penolakan
                                                $keterangan_query = mysqli_query($conn, "
                                                    SELECT keterangan FROM pengajuan_buku 
                                                    WHERE id_pengajuan = '{$row['id_pengajuan']}'
                                                ");
                                                $keterangan_data = mysqli_fetch_assoc($keterangan_query);
                                                if ($keterangan_data && !empty($keterangan_data['keterangan'])):
                                                ?>
                                                    <div class="keterangan-ditolak">
                                                        <strong>Alasan:</strong> <?= htmlspecialchars($keterangan_data['keterangan']) ?>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <form method="post" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pengajuan yang ditolak ini?')" style="display:inline;">
                                                    <input type="hidden" name="id_pengajuan" value="<?= $row['id_pengajuan'] ?>">
                                                    <button type="submit" name="hapus_pengajuan" class="btn-hapus-pengajuan">
                                                        <i class="fas fa-trash"></i> Hapus
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <!-- Mobile Cards View -->
                    <div class="cards-container mobile-view">
                        <?php if (empty($data_ditolak)): ?>
                            <div class="no-data">Tidak ada pengajuan yang ditolak</div>
                        <?php else: ?>
                            <?php foreach ($data_ditolak as $index => $row): ?>
                                <div class="data-card status-ditolak">
                                    <div class="card-header">
                                        <div class="card-number"><?= $index + 1 ?></div>
                                        <div class="card-status">
                                            <span class="badge badge-ditolak">Ditolak</span>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="card-row">
                                            <div class="card-label">Tanggal</div>
                                            <div class="card-value"><?= date('d/m/Y H:i', strtotime($row['tanggal_pengajuan'])) ?></div>
                                        </div>
                                        <div class="card-row">
                                            <div class="card-label">Buku</div>
                                            <div class="card-value"><?= $row['daftar_buku'] ?></div>
                                        </div>
                                        <div class="card-row">
                                            <div class="card-label">Total</div>
                                            <div class="card-value"><strong><?= $row['total_buku'] ?></strong></div>
                                        </div>
                                        <?php
                                        $keterangan_query = mysqli_query($conn, "SELECT keterangan FROM pengajuan_buku WHERE id_pengajuan = '{$row['id_pengajuan']}'");
                                        $keterangan_data = mysqli_fetch_assoc($keterangan_query);
                                        if ($keterangan_data && !empty($keterangan_data['keterangan'])):
                                        ?>
                                            <div class="card-row">
                                                <div class="card-label">Alasan</div>
                                                <div class="card-value"><?= htmlspecialchars($keterangan_data['keterangan']) ?></div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="card-actions">
                                        <form method="post" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pengajuan yang ditolak ini?')" style="display:inline;">
                                            <input type="hidden" name="id_pengajuan" value="<?= $row['id_pengajuan'] ?>">
                                            <button type="submit" name="hapus_pengajuan" class="btn btn-hapus">
                                                <i class="fas fa-trash"></i> Hapus
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                </div>
            </div>
        </div>
    </section>

    <?php include '../partials/footer.php'; ?>

    <script>
        function openTab(evt, tabName) {
            var i, tabcontent, tabs;

            // Hide all tab contents
            tabcontent = document.getElementsByClassName("tabcontent");
            for (i = 0; i < tabcontent.length; i++) {
                tabcontent[i].classList.remove("active");
            }

            // Remove active class from all tabs
            tabs = document.getElementsByClassName("tab");
            for (i = 0; i < tabs.length; i++) {
                tabs[i].classList.remove("active");
            }

            // Show the selected tab content and mark the button as active
            document.getElementById(tabName).classList.add("active");
            evt.currentTarget.classList.add("active");
        }

        // Auto refresh untuk update status real-time
        setInterval(function() {
            // Check if there are pending submissions
            <?php if ($stats['menunggu'] > 0 || $stats['belum_kembali'] > 0): ?>
                // Refresh page setiap 30 detik jika ada status pending
                setTimeout(function() {
                    window.location.reload();
                }, 30000);
            <?php endif; ?>
        }, 1000);

        // JavaScript untuk fungsi tab
        document.addEventListener('DOMContentLoaded', function() {
            // Ambil semua elemen tab
            const tabs = document.querySelectorAll('.tab, .stats-card');
            const tabContents = document.querySelectorAll('.tabcontent');

            // Fungsi untuk menampilkan tab tertentu
            function showTab(targetTabId) {
                // Sembunyikan semua tab content
                tabContents.forEach(content => {
                    content.classList.remove('active');
                });

                // Hapus class active dari semua tab
                tabs.forEach(tab => {
                    tab.classList.remove('active');
                });

                // Tampilkan tab content yang dipilih
                const targetContent = document.getElementById(targetTabId);
                if (targetContent) {
                    targetContent.classList.add('active');
                }

                // Tandai tab yang aktif
                const activeTab = document.querySelector(`[data-tab="${targetTabId}"]`);
                if (activeTab) {
                    activeTab.classList.add('active');
                }
            }

            // Event listener untuk semua tab
            tabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    const targetTab = this.getAttribute('data-tab');
                    if (targetTab) {
                        showTab(targetTab);
                    }
                });
            });

            // Tampilkan tab pertama secara default
            if (tabs.length > 0) {
                const firstTab = tabs[0].getAttribute('data-tab');
                if (firstTab) {
                    showTab(firstTab);
                }
            }
        });
    </script>
</body>

</html>