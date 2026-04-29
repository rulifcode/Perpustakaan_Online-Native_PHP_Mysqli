<?php
session_start();
include '../config/config.php';

session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$id_pengajuan = $_GET['id_pengajuan'] ?? null;
if ($id_pengajuan) {
    // Update status pengajuan jadi 'ditolak'
    $update = mysqli_query($conn, "UPDATE pengajuan_buku SET status='ditolak' WHERE id_pengajuan='$id_pengajuan'");

    if ($update) {
        // Ambil semua detail buku dari pengajuan ini
        $detail = mysqli_query($conn, "SELECT id_buku FROM detail_pengajuan_buku WHERE id_pengajuan='$id_pengajuan'");

        // Tambah stok buku untuk tiap buku di pengajuan ini
        while ($row = mysqli_fetch_assoc($detail)) {
            $id_buku = $row['id_buku'];
            mysqli_query($conn, "UPDATE buku SET stok = stok + 1 WHERE id_buku='$id_buku'");
        }

        echo "<script>alert('Pengajuan ditolak dan stok buku sudah dikembalikan.'); window.location.href='daftar_pengajuan.php';</script>";
    } else {
        echo "<script>alert('Gagal menolak pengajuan.'); window.location.href='pengajuan_list.php';</script>";
    }
} else {
    header("Location: pengajuan_list.php");
}
?>