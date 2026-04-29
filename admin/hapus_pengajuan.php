<?php
include '../config/config.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$id_pengajuan = $_GET['id'] ?? '';

if ($id_pengajuan) {
    $query = mysqli_query($conn, "UPDATE pengajuan_buku SET is_deleted = 1 WHERE id_pengajuan = '$id_pengajuan'");
}

header("Location: pengajuan_list.php");
exit;
?>