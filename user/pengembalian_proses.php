<?php
session_start();
include '../config/config.php';

// Cek login user/admin sesuai kebutuhan
// $id_pengembalian = $_POST['id_pengembalian']; // misal dapat dari form
// Atau $_GET['id_peminjaman']

$id_peminjaman = $_GET['id_peminjaman'] ?? null;

if ($id_peminjaman) {
    // Update status pengembalian jadi lunas / selesai
    $update = mysqli_query($conn, "UPDATE pengembalian SET status_lunas='lunas' WHERE id_peminjaman='$id_peminjaman'");

    if ($update) {
        // Ambil id_buku dari peminjaman
        $peminjaman = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id_buku FROM peminjaman WHERE id_peminjaman='$id_peminjaman'"));

        if ($peminjaman) {
            $id_buku = $peminjaman['id_buku'];

            // Tambah stok buku karena sudah dikembalikan
            mysqli_query($conn, "UPDATE buku SET stok = stok + 1 WHERE id_buku='$id_buku'");
        }

        echo "<script>alert('Buku berhasil dikembalikan dan stok sudah diperbarui.'); window.location.href='pengembalian.php';</script>";
    } else {
        echo "<script>alert('Gagal memproses pengembalian.'); window.location.href='pengembalian.php';</script>";
    }
} else {
    header("Location: pengembalian.php");
}
?>