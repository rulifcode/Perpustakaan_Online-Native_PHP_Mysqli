<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}
include '../config/config.php';

$id_user = $_SESSION['user_id'];
$query = mysqli_query($conn, "
    SELECT pb.id_pengajuan, pb.tanggal_pengajuan, pb.status, b.judul
    FROM pengajuan_buku pb
    JOIN detail_pengajuan_buku dpb ON pb.id_pengajuan = dpb.id_pengajuan
    JOIN buku b ON dpb.id_buku = b.id_buku
    WHERE pb.id_user = '$id_user'
    ORDER BY pb.tanggal_pengajuan DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Riwayat Pengajuan Buku</title>
    <link rel="stylesheet" href="../assets/css/riwayat_pengajuan.css">
    <link rel="stylesheet" href="../assets/css/admin1.css">
</head>
<body>
    <h2>Riwayat Pengajuan Buku</h2>

    <table>
        <thead>
            <tr>
                <th>ID Pengajuan</th>
                <th>Tanggal Pengajuan</th>
                <th>Judul Buku</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (mysqli_num_rows($query) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($query)): ?>
                    <tr>
                        <td><?= $row['id_pengajuan'] ?></td>
                        <td><?= date('d-m-Y', strtotime($row['tanggal_pengajuan'])) ?></td>
                        <td><?= htmlspecialchars($row['judul']) ?></td>
                        <td>
                            <?php
                            if ($row['status'] === 'pending') {
                                echo '<span class="label pending">Menunggu</span>';
                            } elseif ($row['status'] === 'disetujui') {
                                echo '<span class="label approved">Disetujui</span>';
                            } else {
                                echo '<span class="label rejected">Ditolak</span>';
                            }
                            ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="4">Belum ada pengajuan buku.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <p><a href="index.php"><< Kembali ke Beranda</a></p>
</body>
</html>