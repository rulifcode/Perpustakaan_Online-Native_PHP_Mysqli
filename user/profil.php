<?php
session_start();
include '../config/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$errors = [];
$success = "";

// Ambil data user
$result = mysqli_query($conn, "SELECT * FROM users WHERE id_user = $user_id");
$user = mysqli_fetch_assoc($result);

// Proses update profil
if (isset($_POST['update'])) {
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
    $jenis_kelamin = $_POST['jenis_kelamin'];
    $tanggal_lahir = $_POST['tanggal_lahir'];
    $nomor_telepon = mysqli_real_escape_string($conn, $_POST['nomor_telepon']);

    $update = mysqli_query($conn, "UPDATE users SET 
        alamat='$alamat', 
        jenis_kelamin='$jenis_kelamin', 
        tanggal_lahir='$tanggal_lahir', 
        nomor_telepon='$nomor_telepon' 
        WHERE id_user=$user_id");

    if ($update) {
        $success = "Profil berhasil diperbarui.";
        $user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id_user = $user_id"));
    } else {
        $errors[] = "Gagal memperbarui profil.";
    }
}

// Proses upload foto profil
if (isset($_POST['upload_foto'])) {
    if ($_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png'];
        $max_size = 2 * 1024 * 1024; // 2MB

        if (in_array($_FILES['foto']['type'], $allowed_types) && $_FILES['foto']['size'] <= $max_size) {
            $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
            $filename = 'profil_' . $user_id . '.' . $ext;
            $upload_path = '../assets/img/profil/' . $filename;

            // Hapus foto lama jika ada dan beda nama
            if (!empty($user['foto_profil']) && file_exists('../assets/img/profil/' . $user['foto_profil']) && $user['foto_profil'] !== $filename) {
                unlink('../assets/img/profil/' . $user['foto_profil']);
            }

            if (move_uploaded_file($_FILES['foto']['tmp_name'], $upload_path)) {
                mysqli_query($conn, "UPDATE users SET foto_profil='$filename' WHERE id_user=$user_id");
                $success = "Foto profil berhasil diunggah.";
                $user['foto_profil'] = $filename;
            } else {
                $errors[] = "Gagal mengunggah file.";
            }
        } else {
            $errors[] = "File harus JPG/PNG dan maksimal 2MB.";
        }
    } else {
        $errors[] = "Terjadi kesalahan saat upload.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <title>Profil Anda - Litera</title>
    <link rel="icon" type="image/png" href="../assets/img/favicon-32x32.png" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="../assets/css/header.css">
    <link rel="stylesheet" href="../assets/css/footer.css">
    <script src="../assets/js/header.js"></script>
    <script src="../assets/js/animasi.js" defer></script>
    <script src="../assets/js/scroll.js" defer></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    
    <style>
        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8f0 100%);
            color: #333;
            min-height: 100vh;
        }

        /* Hero Section untuk Profil */
        .profile-hero {
            position: relative;
            padding: 4rem 0 2rem;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8f0 100%);
            overflow: hidden;
        }

        .profile-hero-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: relative;
            z-index: 2;
        }

        .profile-hero-text {
            max-width: 500px;
            text-align: center;
            margin: 0 auto; /* Ini yang bikin dia di tengah */
        }


        .welcome-badge {
            display: inline-block;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            padding: 0.5rem 1rem;
            border-radius: 2rem;
            margin-bottom: 1rem;
        }

        .welcome-badge span {
            font-size: 0.9rem;
            font-weight: 600;
            color: #4a6cf7;
        }

        .profile-hero h1 {
            font-size: 3rem;
            margin-bottom: 1rem;
            line-height: 1.2;
            color: #1f3c88;
        }

        .gradient-text {
            background: linear-gradient(90deg, #4a6cf7 0%, #a855f7 100%);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .profile-hero-subtext {
            font-size: 1.2rem;
            color: #64748b;
            margin-bottom: 2rem;
        }

        /* Status Messages */
        .status-messages {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .status-message {
            text-align: center;
            font-weight: bold;
            margin: 10px 0;
            padding: 15px;
            border-radius: 8px;
            animation: slideIn 0.3s ease;
        }

        .status-message.error {
            color: #b00020;
            background: #ffe6e9;
            border: 1px solid #ffcdd2;
        }

        .status-message.success {
            color: #0f5132;
            background: #d1e7dd;
            border: 1px solid #a3cfbb;
        }

        /* Profile Section */
        .profile-section {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
            display: grid;
            grid-template-columns: 350px 1fr;
            gap: 3rem;
            align-items: start;
        }

        /* Enhanced Profile Card - Konsisten dengan Dashboard */
        .profile-card {
            background: white;
            border-radius: 1rem;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            position: sticky;
            top: 2rem;
        }

        .profile-avatar-section {
            text-align: center;
            margin-bottom: 2rem;
        }

        .profile-avatar-container {
            position: relative;
            width: 150px;
            height: 150px;
            margin: 0 auto 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .profile-avatar-container:hover .profile-edit-overlay {
            opacity: 1;
        }

        .profile-avatar-img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #e2e8f0;
            transition: all 0.3s ease;
        }

        .profile-avatar-container:hover .profile-avatar-img {
            filter: brightness(0.8);
        }

        .profile-avatar-default {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: #f1f5f9;
            color: #64748b;
            font-size: 4rem;
            border: 4px solid #e2e8f0;
        }

        .profile-edit-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            border-radius: 50%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
            color: white;
            font-size: 0.8rem;
        }

        .profile-edit-overlay i {
            font-size: 1.5rem;
            margin-bottom: 0.25rem;
        }

        /* Upload Form */
        .upload-form {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1rem;
            margin-top: 1rem;
            padding: 1rem;
            background: #f8fafc;
            border-radius: 0.5rem;
        }

        .file-input-wrapper {
            position: relative;
            overflow: hidden;
            display: inline-block;
        }

        .file-input-wrapper input[type=file] {
            position: absolute;
            left: -9999px;
        }

        .file-input-label {
            display: inline-flex;
            align-items: center;
            padding: 0.75rem 1.5rem;
            background: #f1f5f9;
            color: #64748b;
            border: 2px dashed #cbd5e1;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }

        .file-input-label:hover {
            background: #e2e8f0;
            border-color: #94a3b8;
        }

        .file-input-label i {
            margin-right: 0.5rem;
        }

        .upload-btn {
            background: linear-gradient(135deg, #4a6cf7 0%, #a855f7 100%);
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 0.5rem;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(74, 108, 247, 0.3);
        }

        .upload-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(74, 108, 247, 0.4);
        }

        /* Profile Basic Info */
        .profile-basic-info h2 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            text-align: center;
            color: #1f3c88;
        }

        .profile-detail {
            display: flex;
            align-items: center;
            margin-bottom: 0.75rem;
            color: #64748b;
            font-size: 0.9rem;
        }

        .profile-detail i {
            margin-right: 0.75rem;
            width: 1.25rem;
            text-align: center;
            color: #4a6cf7;
        }

        /* Profile Form */
        .profile-form-section {
            background: white;
            border-radius: 1rem;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        }

        .form-header {
            font-size: 1.5rem;
            font-weight: 600;
            color: #1f3c88;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
        }

        .form-header i {
            margin-right: 0.75rem;
            color: #4a6cf7;
        }

        .form-grid {
            display: grid;
            gap: 1.5rem;
        }

        .form-group {
            display: flex;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .form-group:last-child {
            border-bottom: none;
        }

        .form-label {
            font-weight: 600;
            width: 40%;
            color: #1f3c88;
            display: flex;
            align-items: center;
        }

        .form-label i {
            margin-right: 0.5rem;
            color: #4a6cf7;
            width: 1.25rem;
        }

        .form-value {
            flex: 1;
        }

        .form-value.readonly {
            color: #64748b;
            font-size: 0.95rem;
        }

        input[type="text"],
        input[type="date"],
        select,
        textarea {
            width: 100%;
            padding: 0.75rem 1rem;
            font-size: 0.95rem;
            border-radius: 0.5rem;
            border: 2px solid #e2e8f0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s ease;
        }

        input[type="text"]:focus,
        input[type="date"]:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: #4a6cf7;
            box-shadow: 0 0 0 3px rgba(74, 108, 247, 0.1);
        }

        textarea {
            resize: vertical;
            min-height: 80px;
        }

        .form-actions {
            margin-top: 2rem;
            text-align: center;
        }

        .update-btn {
            background: linear-gradient(135deg, #4a6cf7 0%, #a855f7 100%);
            color: white;
            padding: 1rem 2rem;
            font-weight: 600;
            font-size: 1rem;
            border: none;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(74, 108, 247, 0.3);
            display: inline-flex;
            align-items: center;
        }

        .update-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(74, 108, 247, 0.4);
        }

        .update-btn i {
            margin-right: 0.5rem;
        }

        /* Decorative Elements */
        .hero-decor {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
        }

        .decor-circle {
            position: absolute;
            border-radius: 50%;
            background: rgba(74, 108, 247, 0.1);
        }

        .circle-1 {
            width: 300px;
            height: 300px;
            top: -100px;
            right: -100px;
        }

        .circle-2 {
            width: 200px;
            height: 200px;
            bottom: -50px;
            left: -50px;
            background: rgba(168, 85, 247, 0.1);
        }

        /* Responsive Design */
        @media (max-width: 992px) {
            .profile-hero-content {
                flex-direction: column;
                text-align: center;
            }
            
            .profile-hero h1 {
                font-size: 2rem;
            }
            
            .profile-section {
                grid-template-columns: 1fr;
                gap: 2rem;
            }
            
            .profile-card {
                position: static;
            }
            
            .form-group {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .form-label {
                width: 100%;
                margin-bottom: 0.5rem;
            }
        }

        @media (max-width: 576px) {
            .profile-section {
                padding: 1rem;
            }
            
            .profile-hero {
                padding: 2rem 0 1rem;
            }
        }

        /* Animation */
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-in {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.5s ease;
        }

        .fade-in.visible {
            opacity: 1;
            transform: translateY(0);
        }
    </style>
</head>
<body>
<?php include '../partials/header_user.php'; ?>

<!-- Hero Section -->
<section class="profile-hero">
    <div class="profile-hero-content">
        <div class="profile-hero-text">
            <div class="welcome-badge">
                <span>Kelola Profil Anda</span>
            </div>
            <h1><i class="fas fa-user-circle"></i> Profil <span class="gradient-text"><?= htmlspecialchars($user['nama'] ?? $user['username']) ?></span></h1>
            <p class="profile-hero-subtext">Perbarui informasi profil dan foto Anda di sini</p>
        </div>
    </div>
    
    <!-- Decorative elements -->
    <div class="hero-decor">
        <div class="decor-circle circle-1"></div>
        <div class="decor-circle circle-2"></div>
    </div>
</section>

<!-- Status Messages -->
<div class="status-messages">
    <?php foreach ($errors as $error): ?>
        <div class="status-message error"><?php echo htmlspecialchars($error); ?></div>
    <?php endforeach; ?>

    <?php if ($success): ?>
        <div class="status-message success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
</div>

<!-- Profile Content -->
<div class="profile-section">
    <!-- Profile Card - Konsisten dengan Dashboard -->
    <div class="profile-card fade-in">
        <div class="profile-avatar-section">
            <div class="profile-avatar-container">
                <?php 
                $foto_path = $user['foto_profil'] ? "../assets/img/profil/" . $user['foto_profil'] : "../assets/img/default-avatar.png";
                ?>
                
                <?php if (!empty($user['foto_profil']) && file_exists('../assets/img/profil/' . $user['foto_profil'])): ?>
                    <img src="<?= htmlspecialchars($foto_path) ?>" 
                         alt="Profile Photo" class="profile-avatar-img">
                <?php else: ?>
                    <div class="profile-avatar-default">
                        <i class="fas fa-user-circle"></i>
                    </div>
                <?php endif; ?>
                
                <div class="profile-edit-overlay">
                    <i class="fas fa-camera"></i>
                    <span>Ubah Foto</span>
                </div>
            </div>
            
            <!-- Upload Form -->
            <form method="post" enctype="multipart/form-data" class="upload-form">
                <div class="file-input-wrapper">
                    <input type="file" name="foto" accept="image/png, image/jpeg" required id="foto-input">
                    <label for="foto-input" class="file-input-label">
                        <i class="fas fa-upload"></i>
                        Pilih Foto
                    </label>
                </div>
                <button type="submit" name="upload_foto" class="upload-btn">
                    <i class="fas fa-save"></i>
                    Upload Foto
                </button>
                <small style="color: #64748b; text-align: center;">
                    Format: JPG, PNG. Maksimal 2MB
                </small>
            </form>
        </div>
        
        <!-- Basic Profile Info -->
        <div class="profile-basic-info">
            <h2><?= htmlspecialchars($user['username'] ?? 'Username tidak tersedia') ?></h2>
            <div class="profile-detail">
                <i class="fas fa-envelope"></i>
                <span><?= htmlspecialchars($user['email']) ?></span>
            </div>
            <div class="profile-detail">
                <i class="fas fa-university"></i>
                <span><?= htmlspecialchars($user['asal_institusi'] ?? '-') ?></span>
            </div>
            <div class="profile-detail">
                <i class="fas fa-id-card"></i>
                <span><?= htmlspecialchars($user['identitas'] ?? '-') ?></span>
            </div>
            <div class="profile-detail">
                <i class="fas fa-tag"></i>
                <span><?= htmlspecialchars(ucfirst($user['kategori'])) ?></span>
            </div>
            <div class="profile-detail">
                <i class="fas fa-calendar"></i>
                <span>Bergabung <?= date("d M Y", strtotime($user['created_at'])) ?></span>
            </div>
        </div>
    </div>

    <!-- Profile Form -->
    <div class="profile-form-section fade-in">
        <div class="form-header">
            <i class="fas fa-edit"></i>
            Edit Informasi Profil
        </div>
        
        <form method="post">
            <div class="form-grid">
                <!-- Readonly Fields -->
                <div class="form-group">
                    <div class="form-label">
                        <i class="fas fa-user"></i>
                        Nama Lengkap
                    </div>
                    <div class="form-value readonly"><?= htmlspecialchars($user['nama']) ?></div>
                </div>

                <div class="form-group">
                    <div class="form-label">
                        <i class="fas fa-envelope"></i>
                        Email
                    </div>
                    <div class="form-value readonly"><?= htmlspecialchars($user['email']) ?></div>
                </div>

                <div class="form-group">
                    <div class="form-label">
                        <i class="fas fa-at"></i>
                        Username
                    </div>
                    <div class="form-value readonly"><?= htmlspecialchars($user['username']) ?></div>
                </div>

                <div class="form-group">
                    <div class="form-label">
                        <i class="fas fa-tag"></i>
                        Kategori
                    </div>
                    <div class="form-value readonly"><?= htmlspecialchars(ucfirst($user['kategori'])) ?></div>
                </div>

                <div class="form-group">
                    <div class="form-label">
                        <i class="fas fa-id-card"></i>
                        Identitas
                    </div>
                    <div class="form-value readonly"><?= htmlspecialchars($user['identitas']) ?></div>
                </div>

                <div class="form-group">
                    <div class="form-label">
                        <i class="fas fa-university"></i>
                        Asal Institusi
                    </div>
                    <div class="form-value readonly"><?= htmlspecialchars($user['asal_institusi']) ?></div>
                </div>

                <!-- Editable Fields -->
                <div class="form-group">
                    <div class="form-label">
                        <i class="fas fa-map-marker-alt"></i>
                        Alamat
                    </div>
                    <div class="form-value">
                        <textarea name="alamat" required placeholder="Masukkan alamat lengkap Anda"><?= htmlspecialchars($user['alamat']) ?></textarea>
                    </div>
                </div>

                <div class="form-group">
                    <div class="form-label">
                        <i class="fas fa-venus-mars"></i>
                        Jenis Kelamin
                    </div>
                    <div class="form-value">
                        <select name="jenis_kelamin" required>
                            <option value="L" <?php if ($user['jenis_kelamin'] == 'L') echo 'selected'; ?>>Laki-laki</option>
                            <option value="P" <?php if ($user['jenis_kelamin'] == 'P') echo 'selected'; ?>>Perempuan</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <div class="form-label">
                        <i class="fas fa-birthday-cake"></i>
                        Tanggal Lahir
                    </div>
                    <div class="form-value">
                        <input type="date" name="tanggal_lahir" value="<?= $user['tanggal_lahir'] ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <div class="form-label">
                        <i class="fas fa-phone"></i>
                        Nomor Telepon
                    </div>
                    <div class="form-value">
                        <input type="text" name="nomor_telepon" 
                               value="<?= htmlspecialchars($user['nomor_telepon']) ?>" 
                               required placeholder="Contoh: 081234567890">
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" name="update" class="update-btn">
                    <i class="fas fa-save"></i>
                    Update Profil
                </button>
            </div>
        </form>
    </div>
</div>

<?php include '../partials/footer.php'; ?>

<script>
// Enhanced scroll animation
const fadeElements = document.querySelectorAll('.fade-in');

const revealOnScroll = () => {
    fadeElements.forEach((element, index) => {
        const rect = element.getBoundingClientRect();
        const isVisible = rect.top < window.innerHeight - 100;
        
        if (isVisible) {
            setTimeout(() => {
                element.classList.add('visible');
            }, index * 100);
        }
    });
};

// File input enhancement
const fileInput = document.getElementById('foto-input');
const fileLabel = document.querySelector('.file-input-label');

fileInput.addEventListener('change', function(e) {
    const fileName = e.target.files[0]?.name;
    if (fileName) {
        fileLabel.innerHTML = `<i class="fas fa-check"></i> ${fileName}`;
        fileLabel.style.background = '#e0f2fe';
        fileLabel.style.borderColor = '#4a6cf7';
        fileLabel.style.color = '#4a6cf7';
    }
});

// Initialize animations
document.addEventListener('DOMContentLoaded', function() {
    revealOnScroll();
});

window.addEventListener('scroll', revealOnScroll);
window.addEventListener('load', revealOnScroll);

// Auto-hide success/error messages
document.addEventListener('DOMContentLoaded', function() {
    const messages = document.querySelectorAll('.status-message');
    messages.forEach(message => {
        setTimeout(() => {
            message.style.opacity = '0';
            message.style.transform = 'translateY(-20px)';
            setTimeout(() => {
                message.remove();
            }, 300);
        }, 5000);
    });
});
</script>

</body>
</html>