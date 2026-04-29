<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}
include '../config/config.php';

$detail_id = $_GET['detail_id'] ?? null;
$id_user = $_SESSION['user_id'];

if (!$detail_id) {
    echo "ID pengajuan tidak valid!";
    exit;
}

// Cek dulu kepemilikan dan status pengajuan
$stmt = $conn->prepare("
    SELECT dp.status 
    FROM detail_pengajuan_buku dp 
    JOIN pengajuan_buku pb ON dp.id_pengajuan = pb.id_pengajuan
    WHERE dp.id_detail_pengajuan = ? AND pb.id_user = ?
");
$stmt->bind_param("ii", $detail_id, $id_user);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows == 0) {
    echo "Pengajuan tidak ditemukan atau bukan milik Anda.";
    exit;
}

$data = $res->fetch_assoc();
if (!in_array($data['status'], ['pending', 'ditolak'])) {
    echo "Pengajuan sudah diproses, tidak bisa dihapus.";
    exit;
}

// Hapus pengajuan detail
$del = $conn->prepare("DELETE FROM detail_pengajuan_buku WHERE id_detail_pengajuan = ?");
$del->bind_param("i", $detail_id);
$del->execute();

header("Location: riwayat.php");
exit;
?>