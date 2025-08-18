<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    exit; // hanya untuk admin
}
include '../config/config.php';

$username = mysqli_real_escape_string($conn, $_GET['username'] ?? '');

if ($username === '') {
    echo '<select name="id_peminjaman" required><option value="">-- Pilih --</option></select>';
    exit;
}

$query = mysqli_query($conn, "
    SELECT u.username, p.id_peminjaman, b.judul, p.tanggal_kembali
    FROM peminjaman p
    JOIN pengajuan_buku pb ON p.id_pengajuan = pb.id_pengajuan
    JOIN users u ON pb.id_user = u.id_user
    JOIN detail_pengajuan_buku dp ON pb.id_pengajuan = dp.id_pengajuan AND dp.id_buku = p.id_buku
    JOIN buku b ON dp.id_buku = b.id_buku
    WHERE p.status = 'dipinjam'
    AND u.username LIKE '%$username%'
    AND NOT EXISTS (
        SELECT 1 FROM pengembalian k WHERE k.id_peminjaman = p.id_peminjaman
    )
");

echo '<select name="id_peminjaman" required>';
echo '<option value="">-- Pilih --</option>';
while ($row = mysqli_fetch_assoc($query)) {
    $tgl_kembali = date('d-m-Y', strtotime($row['tanggal_kembali']));
    echo "<option value='{$row['id_peminjaman']}'>{$row['id_peminjaman']} - {$row['username']} - {$row['judul']} (Jatuh Tempo: {$tgl_kembali})</option>";
}
echo '</select>';