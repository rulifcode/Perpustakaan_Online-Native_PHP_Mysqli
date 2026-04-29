<?php
include '../config/config.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    // Simpan hasil query ke dalam $update
    $update = mysqli_query($conn, "UPDATE pengembalian SET status_lunas = 'lunas' WHERE id_pengembalian = '$id'");
    
    if ($update) {
        echo "<script>alert('Status lunas berhasil diperbarui.'); window.location.href='pengembalian.php';</script>";
    } else {
        echo "<script>alert('Gagal memperbarui status.'); window.location.href='pengembalian.php';</script>";
    }
} else {
    header("Location: pengembalian.php");
    exit;
}
?>