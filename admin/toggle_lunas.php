<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}
include '../config/config.php';

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: pengembalian.php");
    exit;
}

$data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT status_lunas FROM pengembalian WHERE id_pengembalian = $id"));
if (!$data) {
    header("Location: pengembalian.php");
    exit;
}

$status_baru = ($data['status_lunas'] === 'lunas') ? 'belum lunas' : 'lunas';

mysqli_query($conn, "UPDATE pengembalian SET status_lunas = '$status_baru' WHERE id_pengembalian = $id");

header("Location: pengembalian.php");
exit;