<?php
session_start();
header('Content-Type: application/json');

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Session tidak valid. Silakan login kembali.']);
    exit;
}

// Cek apakah request method adalah POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method tidak diizinkan.']);
    exit;
}

// Cek apakah parameter yang diperlukan ada
if (!isset($_POST['id']) || !isset($_POST['confirm'])) {
    echo json_encode(['success' => false, 'message' => 'Parameter tidak lengkap.']);
    exit;
}

include '../config/config.php';

$id_pengembalian = (int)$_POST['id'];
$id_user = $_SESSION['user_id'];

try {
    // Begin transaction
    mysqli_begin_transaction($conn);
    
    // Verifikasi bahwa pengembalian ini milik user yang sedang login
    // dan statusnya sudah lunas
    $verify_query = "
        SELECT 
            pg.id_pengembalian,
            pg.id_peminjaman,
            pg.status_lunas,
            pg.denda,
            p.id_pengajuan,
            pb.id_user,
            b.judul
        FROM pengembalian pg
        JOIN peminjaman p ON pg.id_peminjaman = p.id_peminjaman
        JOIN pengajuan_buku pb ON p.id_pengajuan = pb.id_pengajuan
        JOIN buku b ON p.id_buku = b.id_buku
        WHERE pg.id_pengembalian = ? 
        AND pb.id_user = ? 
        AND (pg.is_deleted IS NULL OR pg.is_deleted = 0)
    ";
    
    $stmt = mysqli_prepare($conn, $verify_query);
    mysqli_stmt_bind_param($stmt, "ii", $id_pengembalian, $id_user);
    mysqli_stmt_execute($stmt);
    $verify_result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($verify_result) === 0) {
        throw new Exception('Data pengembalian tidak ditemukan atau Anda tidak memiliki akses.');
    }
    
    $pengembalian_data = mysqli_fetch_assoc($verify_result);
    
    // Cek apakah status sudah lunas
    if (strtolower(trim($pengembalian_data['status_lunas'])) !== 'lunas') {
        throw new Exception('Riwayat hanya dapat dihapus jika status denda sudah lunas.');
    }
    
    // Update field is_deleted menjadi 1 (soft delete)
    $delete_query = "UPDATE pengembalian SET is_deleted = 1, updated_at = NOW() WHERE id_pengembalian = ?";
    $stmt_delete = mysqli_prepare($conn, $delete_query);
    mysqli_stmt_bind_param($stmt_delete, "i", $id_pengembalian);
    
    if (!mysqli_stmt_execute($stmt_delete)) {
        throw new Exception('Gagal menghapus riwayat pengembalian.');
    }
    
    // Commit transaction
    mysqli_commit($conn);
    
    // Log aktivitas (opsional)
    $log_query = "INSERT INTO log_aktivitas (id_user, aktivitas, detail, tanggal) VALUES (?, 'hapus_riwayat', ?, NOW())";
    $stmt_log = mysqli_prepare($conn, $log_query);
    $detail_log = "Menghapus riwayat pengembalian buku: " . $pengembalian_data['judul'];
    mysqli_stmt_bind_param($stmt_log, "is", $id_user, $detail_log);
    mysqli_stmt_execute($stmt_log);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Riwayat pengembalian berhasil dihapus.',
        'data' => [
            'id_pengembalian' => $id_pengembalian,
            'judul_buku' => $pengembalian_data['judul']
        ]
    ]);
    
} catch (Exception $e) {
    // Rollback transaction jika ada error
    mysqli_rollback($conn);
    
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage()
    ]);
} finally {
    // Close statements
    if (isset($stmt)) mysqli_stmt_close($stmt);
    if (isset($stmt_delete)) mysqli_stmt_close($stmt_delete);
    if (isset($stmt_log)) mysqli_stmt_close($stmt_log);
    
    // Close connection
    mysqli_close($conn);
}
?>