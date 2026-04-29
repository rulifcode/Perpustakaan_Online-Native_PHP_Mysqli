<?php
// fungsi.php

// Fungsi untuk membatasi jumlah maksimal buku yang bisa dipinjam berdasarkan kategori user
function getMaksimalPinjam($kategori) {
    switch (strtolower($kategori)) {
        case 'umum':
            return 3;
        case 'siswa':
        case 'mahasiswa':
            return 5;
        case 'guru':
        case 'dosen':
            return 25;
        default:
            return 3; // default kalau tidak dikenali
    }
}

// Fungsi untuk mengecek apakah user masih bisa meminjam
function cekBisaPinjam($conn, $id_user, $kategori) {
    $query = "SELECT COUNT(*) AS total FROM peminjaman 
              WHERE id_user = ? AND status_peminjaman = 'dipinjam'";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id_user);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();

    $totalPinjam = $data['total'];
    $maksimal = getMaksimalPinjam($kategori);

    return $totalPinjam < $maksimal;
}

// Fungsi untuk format tanggal Indonesia (opsional)
function formatTanggal($tanggal) {
    return date('d-m-Y', strtotime($tanggal));
}
?>