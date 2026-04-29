<?php
session_start();
include '../config/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// Cek apakah ada ID buku yang dikirim
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = 'ID buku tidak valid!';
    header("Location: buku.php");
    exit;
}

$id_buku = intval($_GET['id']);

// Ambil data buku berdasarkan ID
$query_buku = "SELECT * FROM buku WHERE id_buku = $id_buku";
$result_buku = mysqli_query($conn, $query_buku);

if (mysqli_num_rows($result_buku) == 0) {
    $_SESSION['error'] = 'Buku tidak ditemukan!';
    header("Location: buku.php");
    exit;
}

$buku = mysqli_fetch_assoc($result_buku);

// Ambil semua kategori untuk dropdown
$query_kategori = "SELECT * FROM kategori ORDER BY nama_kategori ASC";
$result_kategori = mysqli_query($conn, $query_kategori);

// Proses form jika ada POST request
if ($_POST) {
    $judul = mysqli_real_escape_string($conn, trim($_POST['judul']));
    $penulis = mysqli_real_escape_string($conn, trim($_POST['penulis']));
    $penerbit = mysqli_real_escape_string($conn, trim($_POST['penerbit']));
    $tahun_terbit = mysqli_real_escape_string($conn, trim($_POST['tahun_terbit']));
    $id_kategori = intval($_POST['id_kategori']);
    $stok = intval($_POST['stok']);
    
    $errors = [];
    
    // Validasi input
    if (empty($judul)) $errors[] = "Judul buku harus diisi!";
    if (empty($penulis)) $errors[] = "Penulis harus diisi!";
    if (empty($penerbit)) $errors[] = "Penerbit harus diisi!";
    if (empty($tahun_terbit) || !is_numeric($tahun_terbit)) $errors[] = "Tahun terbit harus berupa angka!";
    if ($id_kategori <= 0) $errors[] = "Kategori harus dipilih!";
    if ($stok < 0) $errors[] = "Stok tidak boleh negatif!";
    
    // Handle upload gambar jika ada
    $gambar_baru = $buku['gambar']; // Default tetap gambar lama
    
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        $file_extension = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
        
        if (!in_array($file_extension, $allowed_extensions)) {
            $errors[] = "Format gambar tidak didukung! Gunakan JPG, JPEG, PNG, atau GIF.";
        } elseif ($_FILES['gambar']['size'] > 5000000) { // 5MB
            $errors[] = "Ukuran gambar terlalu besar! Maksimal 5MB.";
        } else {
            // Generate nama file unik
            $gambar_baru = time() . '_' . uniqid() . '.' . $file_extension;
            $upload_path = '../uploads/' . $gambar_baru;
            
            if (!move_uploaded_file($_FILES['gambar']['tmp_name'], $upload_path)) {
                $errors[] = "Gagal mengupload gambar!";
                $gambar_baru = $buku['gambar']; // Kembalikan ke gambar lama
            } else {
                // Hapus gambar lama jika ada dan berbeda
                if (!empty($buku['gambar']) && $buku['gambar'] != $gambar_baru && file_exists('../uploads/' . $buku['gambar'])) {
                    unlink('../uploads/' . $buku['gambar']);
                }
            }
        }
    }
    
    // Jika tidak ada error, update data
    if (empty($errors)) {
        $update_query = "UPDATE buku SET 
            judul = '$judul',
            penulis = '$penulis', 
            penerbit = '$penerbit',
            tahun_terbit = '$tahun_terbit',
            id_kategori = $id_kategori,
            stok = $stok,
            gambar = '$gambar_baru'
            WHERE id_buku = $id_buku";
        
        if (mysqli_query($conn, $update_query)) {
            $_SESSION['success'] = "Buku '$judul' berhasil diperbarui!";
            header("Location: buku.php");
            exit;
        } else {
            $errors[] = "Gagal memperbarui data buku: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Buku - Litera</title>
    
    <link rel="stylesheet" href="../assets/css/admin1.css">
    <link rel="icon" type="image/png" href="../assets/img/favicon-32x32.png" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <style>
        /* Modal Overlay */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(5px);
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: fadeIn 0.3s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes slideUp {
            from { 
                opacity: 0;
                transform: translateY(30px) scale(0.95);
            }
            to { 
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
        
        /* Modal Content */
        .modal-content {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            width: 90%;
            max-width: 700px;
            max-height: 90vh;
            overflow-y: auto;
            animation: slideUp 0.4s ease;
            position: relative;
        }
        
        /* Modal Header */
        .modal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px 30px;
            border-radius: 15px 15px 0 0;
            position: relative;
        }
        
        .modal-header h2 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 600;
        }
        
        .modal-header .close-btn {
            position: absolute;
            top: 20px;
            right: 25px;
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            font-size: 1.5rem;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .modal-header .close-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: rotate(90deg);
        }
        
        /* Modal Body */
        .modal-body {
            padding: 30px;
        }
        
        /* Form Styles */
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .form-group-full {
            grid-column: 1 / -1;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
            font-size: 0.9rem;
        }
        
        .form-group .required {
            color: #e74c3c;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e1e8ed;
            border-radius: 8px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .form-control.error {
            border-color: #e74c3c;
            background: #fdf2f2;
        }
        
        textarea.form-control {
            resize: vertical;
            min-height: 100px;
        }
        
        select.form-control {
            cursor: pointer;
        }
        
        /* File Upload Styles */
        .file-upload-wrapper {
            position: relative;
            border: 2px dashed #ccc;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }
        
        .file-upload-wrapper:hover {
            border-color: #667eea;
            background: #f0f2ff;
        }
        
        .file-upload-wrapper input[type="file"] {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }
        
        .upload-text {
            color: #666;
            font-size: 0.9rem;
        }
        
        .upload-text i {
            font-size: 2rem;
            margin-bottom: 10px;
            color: #667eea;
        }
        
        /* Current Image Preview */
        .current-image {
            margin-top: 15px;
            text-align: center;
        }
        
        .current-image img {
            max-width: 150px;
            max-height: 200px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        .current-image p {
            margin: 10px 0 0 0;
            font-size: 0.85rem;
            color: #666;
        }
        
        /* Button Styles */
        .button-group {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        
        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }
        
        /* Error Alert */
        .alert-error {
            background: #fdf2f2;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .alert-error ul {
            margin: 0;
            padding-left: 20px;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .modal-content {
                width: 95%;
                margin: 10px;
            }
            
            .modal-header,
            .modal-body {
                padding: 20px;
            }
            
            .form-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }
            
            .button-group {
                flex-direction: column;
            }
            
            .btn {
                justify-content: center;
            }
        }
        
        /* Loading State */
        .btn-loading {
            opacity: 0.7;
            cursor: not-allowed;
        }
        
        .btn-loading::after {
            content: "";
            width: 16px;
            height: 16px;
            margin-left: 10px;
            border: 2px solid transparent;
            border-top: 2px solid currentColor;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>

<body>
    <div class="modal-overlay">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header">
                <h2><i class="fas fa-edit"></i> Edit Buku</h2>
                <button type="button" class="close-btn" onclick="closeModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <!-- Modal Body -->
            <div class="modal-body">
                <?php if (!empty($errors)): ?>
                    <div class="alert-error">
                        <strong><i class="fas fa-exclamation-triangle"></i> Terjadi Kesalahan:</strong>
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <form method="POST" enctype="multipart/form-data" id="editBookForm">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="judul">Judul Buku <span class="required">*</span></label>
                            <input type="text" 
                                   id="judul" 
                                   name="judul" 
                                   class="form-control <?= in_array('Judul buku harus diisi!', $errors ?? []) ? 'error' : '' ?>"
                                   value="<?= htmlspecialchars($buku['judul']) ?>"
                                   placeholder="Masukkan judul buku..."
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label for="penulis">Penulis <span class="required">*</span></label>
                            <input type="text" 
                                   id="penulis" 
                                   name="penulis" 
                                   class="form-control <?= in_array('Penulis harus diisi!', $errors ?? []) ? 'error' : '' ?>"
                                   value="<?= htmlspecialchars($buku['penulis']) ?>"
                                   placeholder="Masukkan nama penulis..."
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label for="penerbit">Penerbit <span class="required">*</span></label>
                            <input type="text" 
                                   id="penerbit" 
                                   name="penerbit" 
                                   class="form-control <?= in_array('Penerbit harus diisi!', $errors ?? []) ? 'error' : '' ?>"
                                   value="<?= htmlspecialchars($buku['penerbit']) ?>"
                                   placeholder="Masukkan nama penerbit..."
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label for="tahun_terbit">Tahun Terbit <span class="required">*</span></label>
                            <input type="number" 
                                   id="tahun_terbit" 
                                   name="tahun_terbit" 
                                   class="form-control <?= in_array('Tahun terbit harus berupa angka!', $errors ?? []) ? 'error' : '' ?>"
                                   value="<?= date('Y', strtotime($buku['tahun_terbit'])) ?>"
                                   min="1900" 
                                   max="<?= date('Y') + 1 ?>"
                                   placeholder="<?= date('Y') ?>"
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label for="id_kategori">Kategori <span class="required">*</span></label>
                            <select id="id_kategori" 
                                    name="id_kategori" 
                                    class="form-control <?= in_array('Kategori harus dipilih!', $errors ?? []) ? 'error' : '' ?>"
                                    required>
                                <option value="">-- Pilih Kategori --</option>
                                <?php while ($kategori = mysqli_fetch_assoc($result_kategori)): ?>
                                    <option value="<?= $kategori['id_kategori'] ?>" 
                                            <?= $kategori['id_kategori'] == $buku['id_kategori'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($kategori['nama_kategori']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="stok">Stok <span class="required">*</span></label>
                            <input type="number" 
                                   id="stok" 
                                   name="stok" 
                                   class="form-control <?= in_array('Stok tidak boleh negatif!', $errors ?? []) ? 'error' : '' ?>"
                                   value="<?= $buku['stok'] ?>"
                                   min="0"
                                   placeholder="0"
                                   required>
                        </div>
                    </div>
                    
                    <div class="form-group form-group-full">
                        <label for="gambar">Gambar Buku</label>
                        <div class="file-upload-wrapper">
                            <input type="file" 
                                   id="gambar" 
                                   name="gambar" 
                                   accept="image/jpeg,image/jpg,image/png,image/gif"
                                   onchange="previewImage(this)">
                            <div class="upload-text">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <p><strong>Klik atau drag file ke sini</strong></p>
                                <p>Format: JPG, JPEG, PNG, GIF (Maks. 5MB)</p>
                            </div>
                        </div>
                        
                        <?php if (!empty($buku['gambar'])): ?>
                            <div class="current-image">
                                <img src="../uploads/<?= $buku['gambar'] ?>" 
                                     alt="<?= htmlspecialchars($buku['judul']) ?>"
                                     id="currentImagePreview">
                                <p>Gambar saat ini</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="button-group">
                        <button type="button" class="btn btn-secondary" onclick="closeModal()">
                            <i class="fas fa-times"></i> Batal
                        </button>
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <i class="fas fa-save"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Preview image function
        function previewImage(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const currentImage = document.getElementById('currentImagePreview');
                    if (currentImage) {
                        currentImage.src = e.target.result;
                    } else {
                        // Create new preview if doesn't exist
                        const preview = document.createElement('div');
                        preview.className = 'current-image';
                        preview.innerHTML = `
                            <img src="${e.target.result}" alt="Preview" id="currentImagePreview">
                            <p>Preview gambar baru</p>
                        `;
                        input.parentNode.appendChild(preview);
                    }
                };
                reader.readAsDataURL(input.files[0]);
            }
        }
        
        // Close modal function
        function closeModal() {
            if (confirm('Yakin ingin menutup? Perubahan yang belum disimpan akan hilang.')) {
                window.history.back();
            }
        }
        
        // Form submission with loading state
        document.getElementById('editBookForm').addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.classList.add('btn-loading');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
        });
        
        // Close modal when clicking outside
        document.querySelector('.modal-overlay').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
        
        // Close modal with ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal();
            }
        });
        
        // Prevent modal close when clicking inside modal content
        document.querySelector('.modal-content').addEventListener('click', function(e) {
            e.stopPropagation();
        });
        
        // Auto-focus first input
        document.getElementById('judul').focus();
        
        // Real-time validation
        const requiredFields = ['judul', 'penulis', 'penerbit', 'tahun_terbit', 'id_kategori', 'stok'];
        
        requiredFields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            field.addEventListener('blur', function() {
                if (this.value.trim() === '') {
                    this.classList.add('error');
                } else {
                    this.classList.remove('error');
                }
            });
            
            field.addEventListener('input', function() {
                if (this.value.trim() !== '') {
                    this.classList.remove('error');
                }
            });
        });
    </script>
</body>
</html>