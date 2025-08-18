<?php
session_start();
include '../config/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}


$id_buku = $_GET['id'];

// Hapus dulu dari tabel detail_pengajuan_buku
$query = "DELETE FROM detail_pengajuan_buku WHERE id_buku = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_buku);
$stmt->execute();

// Baru hapus dari tabel buku
$query = "DELETE FROM buku WHERE id_buku = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_buku);
$stmt->execute();

header("Location: buku.php");
exit;
?>