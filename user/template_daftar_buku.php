<?php
include '../config/config.php';

$keyword = isset($_GET['search']) ? $_GET['search'] : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 5;
$offset = ($page - 1) * $limit;

// Total data
$total_query = mysqli_query($conn, "
    SELECT COUNT(*) as total 
    FROM buku 
    WHERE judul LIKE '%" . mysqli_real_escape_string($conn, $keyword) . "%'
");
$total_data = mysqli_fetch_assoc($total_query)['total'];
$total_pages = ceil($total_data / $limit);

// Ambil daftar buku
$query = mysqli_query($conn, "
    SELECT b.*, k.nama_kategori 
    FROM buku b 
    JOIN kategori k ON b.id_kategori = k.id_kategori 
    WHERE b.judul LIKE '%" . mysqli_real_escape_string($conn, $keyword) . "%' 
    LIMIT $limit OFFSET $offset
");

// Proses pengajuan
if (isset($_GET['ajukan'])) {
    $id_buku = (int)$_GET['ajukan'];

    $user_query = mysqli_query($conn, "SELECT * FROM users WHERE id_user = '$id_user'");
    $user_data = mysqli_fetch_assoc($user_query);
    $kategori_user = strtolower($user_data['kategori']);

    // Batasan peminjaman
    switch ($kategori_user) {
        case 'siswa':
        case 'mahasiswa':
            $max_buku = 5;
            break;
        case 'guru':
        case 'dosen':
            $max_buku = 25;
            break;
        default:
            $max_buku = 3;
    }

    // Jumlah buku yang sudah diajukan
    $cek_jumlah = mysqli_query($conn, "
        SELECT COUNT(*) as total 
        FROM pengajuan_buku p 
        JOIN detail_pengajuan_buku dp ON p.id_pengajuan = dp.id_pengajuan 
        WHERE p.id_user = '$id_user' AND p.status IN ('pending', 'approved')
    ");
    $total_ajukan = mysqli_fetch_assoc($cek_jumlah)['total'];

    if ($total_ajukan >= $max_buku) {
        $message = "<span style='color:red;'>Batas maksimal pengajuan buku tercapai ($max_buku buku untuk $kategori_user).</span>";
    } else {
        $cek_stok = mysqli_fetch_assoc(mysqli_query($conn, "SELECT stok FROM buku WHERE id_buku = '$id_buku'"));
        if (!$cek_stok) {
            $message = "<span style='color:red;'>Buku tidak ditemukan.</span>";
        } elseif ($cek_stok['stok'] <= 0) {
            $message = "<span style='color:red;'>Stok buku habis. Tidak bisa diajukan.</span>";
        } else {
            // Cek atau buat pengajuan baru
            $pengajuan_pending = mysqli_query($conn, "SELECT id_pengajuan FROM pengajuan_buku WHERE id_user = '$id_user' AND status = 'pending' LIMIT 1");
            if (mysqli_num_rows($pengajuan_pending) > 0) {
                $id_pengajuan = mysqli_fetch_assoc($pengajuan_pending)['id_pengajuan'];
            } else {
                mysqli_query($conn, "INSERT INTO pengajuan_buku(id_user, tanggal_pengajuan, status) VALUES('$id_user', NOW(), 'pending')");
                $id_pengajuan = mysqli_insert_id($conn);
            }

            $judul_buku = mysqli_fetch_assoc(mysqli_query($conn, "SELECT judul FROM buku WHERE id_buku = '$id_buku'"))['judul'];
            $boleh_ajukan = true;

            // Non-pengajar tidak boleh ajukan judul sama
            if (!in_array($kategori_user, ['guru', 'dosen'])) {
                $cek_judul = mysqli_query($conn, "
                    SELECT b.judul 
                    FROM pengajuan_buku p
                    JOIN detail_pengajuan_buku dp ON p.id_pengajuan = dp.id_pengajuan
                    JOIN buku b ON dp.id_buku = b.id_buku
                    WHERE p.id_user = '$id_user' 
                    AND b.judul = '" . mysqli_real_escape_string($conn, $judul_buku) . "'
                    AND p.status = 'pending'
                ");
                if (mysqli_num_rows($cek_judul) > 0) {
                    $boleh_ajukan = false;
                    $message = "<span style='color:red;'>Anda sudah mengajukan buku dengan judul yang sama.</span>";
                }
            }

            if ($boleh_ajukan) {
                mysqli_query($conn, "INSERT INTO detail_pengajuan_buku(id_pengajuan, id_buku) VALUES('$id_pengajuan', '$id_buku')");
                mysqli_query($conn, "UPDATE buku SET stok = stok - 1 WHERE id_buku = '$id_buku'");
                $message = "<span style='color:green;'>Berhasil mengajukan buku.</span>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Daftar Buku</title>
    <link rel="stylesheet" href="../assets/css/daftar_buku_user.css">
</head>
<body>
    <div class="container">
        <h2>Daftar Buku</h2>
        <form method="get" class="search-form">
            <input type="text" name="search" placeholder="Cari buku..." value="<?php echo htmlspecialchars($keyword); ?>">
            <button type="submit">Cari</button>
        </form>

        <?php if (isset($message)) echo "<p class='message'>$message</p>"; ?>

        <ul class="buku-grid">
        <?php while ($row = mysqli_fetch_assoc($query)) { ?>
            <li class="buku-card">
                <?php if (!empty($row['gambar'])): ?>
                    <img src="../uploads/<?php echo htmlspecialchars($row['gambar']); ?>" alt="Gambar">
                <?php else: ?>
                    <img src="../assets/images/no-image.png" alt="No Image">
                <?php endif; ?>

                <div class="overlay">
                    <h4><?php echo htmlspecialchars($row['judul']); ?></h4>
                    <p>Kategori: <?php echo htmlspecialchars($row['nama_kategori']); ?></p>
                    <p>Penulis: <?php echo htmlspecialchars($row['penulis']); ?></p>
                    <p>Penerbit: <?php echo htmlspecialchars($row['penerbit']); ?></p>
                    <p>Stok: <?php echo $row['stok']; ?></p>
                </div>

                <div class="buku-info">
                    <h3><?php echo htmlspecialchars($row['judul']); ?></h3>
                    <p><?php echo htmlspecialchars($row['penulis']); ?></p>
                    <?php if ($row['stok'] > 0): ?>
                        <a class="ajukan-link" href="?ajukan=<?php echo $row['id_buku']; ?>&search=<?php echo urlencode($keyword); ?>&page=<?php echo $page; ?>" onclick="return confirm('Ajukan buku ini?')">Ajukan</a>
                    <?php else: ?>
                        <span class="stok-habis">Stok habis</span>
                    <?php endif; ?>
                </div>
            </li>
        <?php } ?>
        </ul>

        <div class="pagination">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a class="page-link <?php if ($i == $page) echo 'active'; ?>" href="?search=<?php echo urlencode($keyword); ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
            <?php endfor; ?>
        </div>

        <p><a href="index.php"><< Kembali ke Beranda</a></p>
    </div>
</body>
</html>