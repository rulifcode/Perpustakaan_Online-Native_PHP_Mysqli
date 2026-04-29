<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

include '../config/config.php';

if (isset($_GET['id']) && isset($_GET['aksi'])) {
    $id_pengajuan = intval($_GET['id']);
    $aksi = $_GET['aksi'];

    //Ambil status pengajuan
    $stmt = $conn->prepare("SELECT status FROM pengajuan_buku WHERE id_pengajuan = ?");
    $stmt->bind_param("i", $id_pengajuan);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        header("Location: pengajuan_list.php?error=Pengajuan tidak ditemukan");
        exit;
    }
    
    $status = $result->fetch_assoc()['status'];
    if ($status !== 'pending') {
        header("Location: pengajuan_list.php?error=Pengajuan sudah diverifikasi sebelumnya");
        exit;
    }

    if ($aksi === 'setujui') {
        //Ambil data user dan daftar buku
        $stmt = $conn->prepare("
            SELECT pb.id_user, dpb.id_buku, dpb.jumlah, b.judul
            FROM pengajuan_buku pb
            JOIN detail_pengajuan_buku dpb ON pb.id_pengajuan = dpb.id_pengajuan
            JOIN buku b ON dpb.id_buku = b.id_buku
            WHERE pb.id_pengajuan = ?
        ");
        $stmt->bind_param("i", $id_pengajuan);
        $stmt->execute();
        $res = $stmt->get_result();

        $id_user = null;
        $buku_list = [];
        $judul_count = [];

        while ($row = $res->fetch_assoc()) {
            $id_user = $row['id_user'];
            $buku_list[] = [
                'id_buku' => $row['id_buku'],
                'jumlah' => $row['jumlah']
            ];
            
            //Hitung jumlah per judul
            $judul = $row['judul'];
            $judul_count[$judul] = ($judul_count[$judul] ?? 0) + $row['jumlah'];
        }

        //Ambil kategori user
        $stmt = $conn->prepare("SELECT kategori FROM users WHERE id_user = ?");
        $stmt->bind_param("i", $id_user);
        $stmt->execute();
        $user_data = $stmt->get_result()->fetch_assoc();
        $kategori = $user_data['kategori'];

        //Validasi berdasarkan kategori
        $total_buku = array_sum(array_column($buku_list, 'jumlah'));
        $max_duplikat_judul = !empty($judul_count) ? max($judul_count) : 0;

        if ($kategori === 'pelajar' || $kategori === 'umum') {
            if ($total_buku > 5) {
                header("Location: pengajuan_list.php?error=Pelajar/Umum hanya boleh maksimal 5 buku");
                exit;
            }
            if ($max_duplikat_judul > 1) {
                header("Location: pengajuan_list.php?error=Pelajar/Umum hanya boleh 1 eksemplar per judul");
                exit;
            }
        }

        if ($kategori === 'pengajar') {
            //Cek jumlah buku yang sedang dipinjam
            $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM peminjaman WHERE id_user = ? AND status = 'dipinjam'");
            $stmt->bind_param("i", $id_user);
            $stmt->execute();
            $dipinjam = $stmt->get_result()->fetch_assoc()['total'];

            if (($dipinjam + $total_buku) > 25) {
                header("Location: pengajuan_list.php?error=Pengajar hanya boleh pinjam maksimal 25 buku");
                exit;
            }
        }

        $conn->begin_transaction();
        
        try {
            //Update status pengajuan
            $stmt = $conn->prepare("UPDATE pengajuan_buku SET status = 'disetujui' WHERE id_pengajuan = ?");
            $stmt->bind_param("i", $id_pengajuan);
            if (!$stmt->execute()) {
                throw new Exception("Error updating pengajuan status");
            }

            //Ambil setting hari batas berdasarkan kategori user
            $setting_query = $conn->query("SELECT hari_batas, hari_batas_pengajar FROM setting_denda LIMIT 1");
            
            if ($setting_query && $setting_query->num_rows > 0) {
                $setting = $setting_query->fetch_assoc();
                $hari_batas_pelajar = $setting['hari_batas'];
                $hari_batas_pengajar = $setting['hari_batas_pengajar'];
            } else {
                // Default values jika setting belum ada
                $hari_batas_pelajar = 7;
                $hari_batas_pengajar = 14;
            }

            //Tentukan hari batas berdasarkan kategori user
            if ($kategori === 'pengajar') {
                $hari_batas = $hari_batas_pengajar;
            } else {
                //Untuk pelajar dan umum
                $hari_batas = $hari_batas_pelajar;
            }

            $tanggal_pinjam = date('Y-m-d');
            $tanggal_kembali = date('Y-m-d', strtotime("+$hari_batas days"));

            // Prepared statements untuk insert peminjaman dan update stok
            $stmt_insert = $conn->prepare("
                INSERT INTO peminjaman (id_pengajuan, id_user, id_buku, tanggal_pinjam, tanggal_kembali, status)
                VALUES (?, ?, ?, ?, ?, 'dipinjam')
            ");
            $stmt_update_stok = $conn->prepare("UPDATE buku SET stok = stok - ? WHERE id_buku = ?");

            // Process setiap buku dalam pengajuan
            foreach ($buku_list as $item) {
                $id_buku = $item['id_buku'];
                $jumlah = $item['jumlah'];

                // Cek ketersediaan stok
                $stmt_cek = $conn->prepare("SELECT stok FROM buku WHERE id_buku = ?");
                $stmt_cek->bind_param("i", $id_buku);
                $stmt_cek->execute();
                $stok_result = $stmt_cek->get_result();
                
                if ($stok_result->num_rows === 0) {
                    throw new Exception("Buku dengan ID $id_buku tidak ditemukan");
                }
                
                $stok_tersedia = $stok_result->fetch_assoc()['stok'];
                if ($stok_tersedia < $jumlah) {
                    throw new Exception("Stok buku ID $id_buku tidak mencukupi");
                }

                // Insert record peminjaman sejumlah eksemplar yang dipinjam
                for ($i = 0; $i < $jumlah; $i++) {
                    $stmt_insert->bind_param("iiiss", $id_pengajuan, $id_user, $id_buku, $tanggal_pinjam, $tanggal_kembali);
                    if (!$stmt_insert->execute()) {
                        throw new Exception("Error inserting peminjaman for book ID $id_buku");
                    }
                }

                // Update stok buku (kurangi sesuai jumlah yang dipinjam)
                $stmt_update_stok->bind_param("ii", $jumlah, $id_buku);
                if (!$stmt_update_stok->execute()) {
                    throw new Exception("Error updating stock for book ID $id_buku");
                }
            }

            // Commit transaction
            $conn->commit();
            
        } catch (Exception $e) {
            // Rollback transaction
            $conn->rollback();
            header("Location: pengajuan_list.php?error=" . urlencode($e->getMessage()));
            exit;
        }

    } elseif ($aksi === 'tolak') {
        // Begin transaction
        $conn->begin_transaction();
        
        try {
            // Update status pengajuan menjadi ditolak
            $stmt = $conn->prepare("UPDATE pengajuan_buku SET status = 'ditolak' WHERE id_pengajuan = ?");
            $stmt->bind_param("i", $id_pengajuan);
            if (!$stmt->execute()) {
                throw new Exception("Error updating pengajuan status");
            }

            // Kembalikan stok buku (jika sebelumnya sudah dikurangi saat pengajuan)
            $stmt = $conn->prepare("SELECT id_buku, jumlah FROM detail_pengajuan_buku WHERE id_pengajuan = ?");
            $stmt->bind_param("i", $id_pengajuan);
            $stmt->execute();
            $res = $stmt->get_result();

            $stmt_update_stok = $conn->prepare("UPDATE buku SET stok = stok + ? WHERE id_buku = ?");
            while ($row = $res->fetch_assoc()) {
                $stmt_update_stok->bind_param("ii", $row['jumlah'], $row['id_buku']);
                if (!$stmt_update_stok->execute()) {
                    throw new Exception("Error restoring stock for book ID " . $row['id_buku']);
                }
            }

            //Commit transaction
            $conn->commit();
            
        } catch (Exception $e) {
            // Rollback transaction
            $conn->rollback();
            header("Location: pengajuan_list.php?error=" . urlencode($e->getMessage()));
            exit;
        }
    }

    header("Location: pengajuan_list.php?success=Verifikasi berhasil");
    exit;
}
?>