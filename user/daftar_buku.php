<?php
session_start();
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

// Pencarian & pagination
$keyword = $_GET['search'] ?? '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 5;
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
    <title>Daftar Buku</title>
    <link rel="stylesheet" href="../assets/css/daftar_buku.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">
    <h2>Daftar Buku</h2>
    <form method="get" class="search-form">
        <input type="text" name="search" value="<?= htmlspecialchars($keyword) ?>" placeholder="Cari buku...">
        <button type="submit">Cari</button>
    </form>

    <?php if (isset($_SESSION['message'])): ?>
        <p class="message"><?= $_SESSION['message'] ?></p>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>

    <ul class="buku-grid">
        <?php while ($b = mysqli_fetch_assoc($booksRes)): ?>
            <li class="buku-card">
                <img src="../uploads/<?= htmlspecialchars($b['gambar'] ?: 'no-image.png') ?>" alt="<?= htmlspecialchars($b['judul']) ?>">
                <div class="buku-info">
                    <h3><?= htmlspecialchars($b['judul']) ?></h3>
                    <p><?= htmlspecialchars($b['penulis']) ?></p>
                    <p>Stok: <?= $b['stok'] ?></p>
                    <?php if ($b['stok'] > 0): ?>
                        <form class="ajukan-form" method="get" onsubmit="return confirm('Ajukan buku ini?');">
                            <input type="hidden" name="search" value="<?= htmlspecialchars($keyword) ?>">
                            <input type="hidden" name="page" value="<?= $page ?>">
                            <input type="hidden" name="ajukan" value="<?= $b['id_buku'] ?>">
                            <?php if ($kategori === 'pengajar'): ?>
                                <label>Qty:</label>
                                <input type="number" name="qty" min="1" max="<?= $b['stok'] ?>" value="1" required>
                            <?php endif; ?>
                            <button type="submit">Ajukan</button>
                        </form>
                    <?php else: ?>
                        <span style="color:red;">Stok habis</span>
                    <?php endif; ?>
                </div>
            </li>
        <?php endwhile; ?>
    </ul>

    <div class="pagination">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a class="page-link <?= $i === $page ? 'active' : '' ?>" href="?search=<?= urlencode($keyword) ?>&page=<?= $i ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>

    <p><a href="index.php">&laquo; Kembali ke Beranda</a></p>
</div>
</body>
</html>