<?php
session_start();
header('Content-Type: application/json');

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Anda harus login terlebih dahulu'
    ]);
    exit;
}

include '../config/config.php';

// Cek apakah request method POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method tidak diperbolehkan'
    ]);
    exit;
}

$id_user = $_SESSION['user_id'];

try {
    // Mulai transaction
    mysqli_begin_transaction($conn);
    
    // Ambil semua data yang akan dihapus untuk logging
    $check_query = mysqli_query($conn, "
        SELECT 
            p.id_peminjaman,
            p.id_pengajuan,
            b.judul,
            pg.id_pengembalian
        FROM peminjaman p
        JOIN pengajuan_buku pb ON p.id_pengajuan = pb.id_pengajuan
        JOIN buku b ON p.id_buku = b.id_buku
        JOIN pengembalian pg ON p.id_peminjaman = pg.id_peminjaman 
        WHERE pb.id_user = '$id_user' 
        AND pb.status = 'disetujui'
        AND pg.status_lunas = 'lunas'
        AND (pg.is_deleted IS NULL OR pg.is_deleted = 0)
    ");
    
    if (!$check_query) {
        throw new Exception('Error saat mengecek data: ' . mysqli_error($conn));
    }
    
    $data_to_delete = [];
    while ($row = mysqli_fetch_assoc($check_query)) {
        $data_to_delete[] = $row;
    }
    
    if (empty($data_to_delete)) {
        echo json_encode([
            'success' => false,
            'message' => 'Tidak ada data yang bisa dihapus'
        ]);
        exit;
    }
    
    // Update is_deleted = 1 untuk semua pengembalian yang sudah lunas
    $update_query = mysqli_query($conn, "
        UPDATE pengembalian pg
        JOIN peminjaman p ON pg.id_peminjaman = p.id_peminjaman
        JOIN pengajuan_buku pb ON p.id_pengajuan = pb.id_pengajuan
        SET pg.is_deleted = 1, pg.is_deleted = NOW()
        WHERE pb.id_user = '$id_user' 
        AND pb.status = 'disetujui'
        AND pg.status_lunas = 'lunas'
        AND (pg.is_deleted IS NULL OR pg.is_deleted = 0)
    ");
    
    if (!$update_query) {
        throw new Exception('Error saat menghapus data: ' . mysqli_error($conn));
    }
    
    $affected_rows = mysqli_affected_rows($conn);
    
    if ($affected_rows > 0) {
        // Log aktivitas penghapusan (optional)
        $log_query = mysqli_query($conn, "
            INSERT INTO log_aktivitas (id_user, aktivitas, detail, tanggal) 
            VALUES (
                '$id_user', 
                'hapus_riwayat_massal', 
                'Menghapus $affected_rows riwayat peminjaman yang sudah lunas', 
                NOW()
            )
        ");
        
        // Commit transaction
        mysqli_commit($conn);
        
        echo json_encode([
            'success' => true,
            'message' => "Berhasil menghapus $affected_rows riwayat peminjaman",
            'deleted_count' => $affected_rows,
            'redirect' => true
        ]);
    } else {
        throw new Exception('Tidak ada data yang berhasil dihapus');
    }
    
} catch (Exception $e) {
    // Rollback transaction jika ada error
    mysqli_rollback($conn);
    
    error_log("Error hapus riwayat: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan: ' . $e->getMessage()
    ]);
}

// Tutup koneksi
mysqli_close($conn);
?>