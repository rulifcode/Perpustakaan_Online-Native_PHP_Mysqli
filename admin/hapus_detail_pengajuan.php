<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}


include '../config/config.php';

$detail_id = $_GET['detail_id'] ?? null;

if (!$detail_id) {
    echo "ID detail pengajuan tidak valid!";
    exit;
}

// Hapus peminjaman terkait jika ada
$conn->query("DELETE FROM peminjaman WHERE id_pengajuan IN (SELECT id_pengajuan FROM detail_pengajuan_buku WHERE id_detail_pengajuan = $detail_id) AND id_buku = (SELECT id_buku FROM detail_pengajuan_buku WHERE id_detail_pengajuan = $detail_id)");

// Hapus detail pengajuan
$delete = $conn->prepare("DELETE FROM detail_pengajuan_buku WHERE id_detail_pengajuan = ?");
$delete->bind_param("i", $detail_id);
$delete->execute();

header("Location: pengajuan_list.php");
exit;
?>