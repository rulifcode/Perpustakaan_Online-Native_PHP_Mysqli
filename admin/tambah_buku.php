<?php
session_start();
include '../config/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// Ambil semua kategori untuk dropdown
$query_kategori = "SELECT * FROM kategori ORDER BY nama_kategori ASC";
$result_kategori = mysqli_query($conn, $query_kategori);

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $judul = mysqli_real_escape_string($conn, $_POST['judul']);
    $penulis = mysqli_real_escape_string($conn, $_POST['penulis']);
    $penerbit = mysqli_real_escape_string($conn, $_POST['penerbit']);
    $tahun_terbit = mysqli_real_escape_string($conn, $_POST['tahun_terbit']);
    $stok = intval($_POST['stok']);
    $id_kategori = intval($_POST['id_kategori']);
    
    // Validasi input
    if (empty($judul) || empty($penulis) || empty($penerbit) || empty($tahun_terbit)) {
        $error = "Semua field wajib diisi!";
    } else {
        // Handle upload gambar
        $gambar = '';
        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
            $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            $file_type = $_FILES['gambar']['type'];
            
            if (in_array($file_type, $allowed_types)) {
                $file_extension = pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION);
                $gambar = 'buku_' . time() . '.' . $file_extension;
                $upload_path = '../uploads/' . $gambar;
                
                if (!move_uploaded_file($_FILES['gambar']['tmp_name'], $upload_path)) {
                    $error = "Gagal mengupload gambar!";
                    $gambar = '';
                }
            } else {
                $error = "Format gambar tidak valid! Gunakan JPG, PNG, atau GIF.";
            }
        }
        
        if (empty($error)) {
            // Modified query to match current database structure
            $query = "INSERT INTO buku (judul, penulis, penerbit, tahun_terbit, gambar, id_kategori, stok) 
                     VALUES ('$judul', '$penulis', '$penerbit', '$tahun_terbit', '$gambar', $id_kategori, $stok)";
            
            if (mysqli_query($conn, $query)) {
                $success = "Buku berhasil ditambahkan!";
                // Reset form
                $_POST = array();
            } else {
                $error = "Gagal menambahkan buku: " . mysqli_error($conn);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
  <title>Dashboard Admin</title>
  <link rel="stylesheet" href="../assets/css/admin1.css">
  <link rel="stylesheet" href="../assets/css/sidebar.css">
  <link rel="stylesheet" href="../assets/js/header_admin.js">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</head>
<body>
    <?php include 'header.php'; ?>
    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <div class="page-header">
            <h2 class="dashboard-title">Tambah Buku Baru</h2>
            <a href="buku.php" class="button secondary">
                <i class="fas fa-arrow-left"></i> Kembali ke Daftar Buku
            </a>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?= $success ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?= $error ?>
            </div>
        <?php endif; ?>

        <div class="form-container">
            <form method="POST" enctype="multipart/form-data" class="form-buku">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="judul">Judul Buku <span class="required">*</span></label>
                        <input type="text" id="judul" name="judul" required 
                               value="<?= isset($_POST['judul']) ? htmlspecialchars($_POST['judul']) : '' ?>"
                               placeholder="Masukkan judul buku">
                    </div>

                    <div class="form-group">
                        <label for="penulis">Penulis <span class="required">*</span></label>
                        <input type="text" id="penulis" name="penulis" required 
                               value="<?= isset($_POST['penulis']) ? htmlspecialchars($_POST['penulis']) : '' ?>"
                               placeholder="Masukkan nama penulis">
                    </div>

                    <div class="form-group">
                        <label for="penerbit">Penerbit <span class="required">*</span></label>
                        <input type="text" id="penerbit" name="penerbit" required 
                               value="<?= isset($_POST['penerbit']) ? htmlspecialchars($_POST['penerbit']) : '' ?>"
                               placeholder="Masukkan nama penerbit">
                    </div>

                    <div class="form-group">
                        <label for="tahun_terbit">Tahun Terbit <span class="required">*</span></label>
                        <input type="date" id="tahun_terbit" name="tahun_terbit" required 
                               value="<?= isset($_POST['tahun_terbit']) ? $_POST['tahun_terbit'] : '' ?>">
                    </div>

                               value="<?= isset($_POST['isbn']) ? htmlspecialchars($_POST['isbn']) : '' ?>
                    <div class="form-group">
                        <label for="stok">Stok <span class="required">*</span></label>
                        <input type="number" id="stok" name="stok" min="0" required 
                               value="<?= isset($_POST['stok']) ? $_POST['stok'] : '1' ?>"
                               placeholder="Jumlah stok buku">
                    </div>

                    <div class="form-group">
                        <label for="id_kategori">Kategori <span class="required">*</span></label>
                        <select id="id_kategori" name="id_kategori" required>
                            <option value="">Pilih Kategori</option>
                            <?php mysqli_data_seek($result_kategori, 0); ?>
                            <?php while ($kat = mysqli_fetch_assoc($result_kategori)): ?>
                                <option value="<?= $kat['id_kategori'] ?>" 
                                        <?= (isset($_POST['id_kategori']) && $_POST['id_kategori'] == $kat['id_kategori']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($kat['nama_kategori']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="gambar">Gambar Buku</label>
                        <input type="file" id="gambar" name="gambar" accept="image/*">
                        <small class="form-hint">Format: JPG, PNG, GIF. Maksimal 2MB.</small>
                    </div>
                </div>
                <?= isset($_POST['deskripsi']) ? htmlspecialchars($_POST['deskripsi']) : '' ?></textarea>
                </div>
                <div class="form-actions">
                    <button type="submit" class="button primary">
                        <i class="fas fa-save"></i> Simpan Buku
                    </button>
                    <a href="buku.php" class="button secondary">
                        <i class="fas fa-times"></i> Batal
                    </a>
                </div>
            </form>
        </div>
    </div>

    <footer>
        &copy; <?= date('Y') ?> Perpustakaan - Litera
    </footer>

    <script>
        // Preview gambar sebelum upload
        document.getElementById('gambar').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Validasi ukuran file (2MB)
                if (file.size > 2 * 1024 * 1024) {
                    alert('Ukuran file terlalu besar! Maksimal 2MB.');
                    e.target.value = '';
                    return;
                }

                // Validasi tipe file
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                if (!allowedTypes.includes(file.type)) {
                    alert('Format file tidak didukung! Gunakan JPG, PNG, atau GIF.');
                    e.target.value = '';
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(e) {
                    // Buat preview jika belum ada
                    let preview = document.getElementById('image-preview');
                    if (!preview) {
                        preview = document.createElement('div');
                        preview.id = 'image-preview';
                        preview.innerHTML = '<p><strong>Preview:</strong></p><img id="preview-img" style="max-width: 150px; max-height: 200px; margin-top: 10px; border-radius: 4px; border: 1px solid #ddd;">';
                        e.target.parentNode.appendChild(preview);
                    }
                    document.getElementById('preview-img').src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });

        // Validasi form sebelum submit
        document.querySelector('.form-buku').addEventListener('submit', function(e) {
            const judul = document.getElementById('judul').value.trim();
            const penulis = document.getElementById('penulis').value.trim();
            const penerbit = document.getElementById('penerbit').value.trim();
            const tahun_terbit = document.getElementById('tahun_terbit').value;
            const stok = parseInt(document.getElementById('stok').value);
            const kategori = document.getElementById('id_kategori').value;

            let errors = [];

            if (judul.length < 3) {
                errors.push('Judul buku minimal 3 karakter');
            }

            if (penulis.length < 2) {
                errors.push('Nama penulis minimal 2 karakter');
            }

            if (penerbit.length < 2) {
                errors.push('Nama penerbit minimal 2 karakter');
            }

            if (!tahun_terbit) {
                errors.push('Tahun terbit harus diisi');
            }

            if (isNaN(stok) || stok < 0) {
                errors.push('Stok harus berupa angka positif');
            }

            if (!kategori) {
                errors.push('Kategori harus dipilih');
            }

            if (errors.length > 0) {
                e.preventDefault();
                alert('Terjadi kesalahan:\n\n' + errors.join('\n'));
                return false;
            }
        });

        // Auto hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                alert.style.opacity = '0';
                alert.style.transition = 'opacity 0.5s ease';
                setTimeout(function() {
                    alert.style.display = 'none';
                }, 500);
            });
        }, 5000);
        
    </script>
</body>
</html>