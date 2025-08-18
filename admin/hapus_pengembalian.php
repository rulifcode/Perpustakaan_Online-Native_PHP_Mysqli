<?php
include '../config/config.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Cek apakah data ada sebelum hapus
    $cek = mysqli_query($conn, "SELECT * FROM pengembalian WHERE id_pengembalian = '$id'");
    if (mysqli_num_rows($cek) > 0) {
        if (mysqli_query($conn, "DELETE FROM pengembalian WHERE id_pengembalian = '$id'")) {
            $_SESSION['msg'] = "Data pengembalian berhasil dihapus.";
        } else {
            $_SESSION['msg'] = "Gagal menghapus data.";
        }
    } else {
        $_SESSION['msg'] = "Data tidak ditemukan.";
    }
} else {
    $_SESSION['msg'] = "ID tidak valid.";
}

header("Location: pengembalian.php");
exit;
?>