<?php
include '../../config/config.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_detail = $_POST['id_detail'];
    $update = mysqli_query($conn, "UPDATE detail_peminjaman SET status_lunas = 'lunas' WHERE id_detail = '$id_detail'");
    header("Location: laporan_pengembalian.php");
}