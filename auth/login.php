<?php
session_start();
include '../config/config.php';

$error = '';

if (isset($_POST['login'])) {

    $input    = trim($_POST['username']);
    $password = $_POST['password'];

    /* =========================
       LOGIN ADMIN
    ========================== */
    $stmtAdmin = $conn->prepare("SELECT * FROM admin WHERE username = ?");
    $stmtAdmin->bind_param("s", $input);
    $stmtAdmin->execute();
    $resultAdmin = $stmtAdmin->get_result();

    if ($resultAdmin->num_rows > 0) {

        $admin = $resultAdmin->fetch_assoc();

        if (password_verify($password, $admin['password'])) {

            $_SESSION['user_id']  = $admin['id'];
            $_SESSION['username'] = $admin['username'];
            $_SESSION['nama']     = $admin['nama'];
            $_SESSION['role']     = 'admin';

            header("Location: ../admin/index.php");
            exit;
        } else {
            $error = "Password salah.";
        }

    } else {

        /* =========================
           LOGIN USER
        ========================== */
        $stmtUser = $conn->prepare("
            SELECT * FROM users 
            WHERE username = ? 
            OR email = ? 
            OR identitas = ?
        ");

        $stmtUser->bind_param("sss", $input, $input, $input);
        $stmtUser->execute();
        $resultUser = $stmtUser->get_result();

        if ($resultUser->num_rows > 0) {

            $user = $resultUser->fetch_assoc();

            if (password_verify($password, $user['password'])) {

                $_SESSION['user_id']  = $user['id_user'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['nama']     = $user['nama'] ?? $user['username'];
                $_SESSION['role']     = 'user';

                header("Location: ../user/index.php");
                exit;

            } else {
                $error = "Password salah.";
            }

        } else {
            $error = "Akun tidak ditemukan.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Masuk — Litera</title>

<link rel="icon" type="image/png" href="../assets/img/favicon-32x32.png">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/login.css">
</head>

<body>

<canvas id="stars"></canvas>

<div class="login-wrapper">

    <!-- ══ LEFT BRAND ══ -->
    <div class="login-side-brand">

        <a href="../index.php" class="brand-logo">
            <img src="../assets/img/logoTr.png" alt="Litera">
            <div class="brand-logo-text">
                <span class="brand-name">Litera</span>
                <span class="brand-sub">Digital Library Experience</span>
            </div>
        </a>

        <div class="brand-hero">
            <div class="brand-eyebrow">
                <span></span>
                Perpustakaan Digital
                <span></span>
            </div>

            <h1>Masuk dan lanjutkan<br><em>perjalanan membaca.</em></h1>

            <p class="brand-desc">
                Akses koleksi digital, riwayat peminjaman, serta pengalaman 
                perpustakaan modern dalam satu platform yang elegan.
            </p>
        </div>

        <div class="brand-feature-list">
            <div>
                <div class="feature-icon">
                    <i class="fa-solid fa-book-open"></i>
                </div>
                Ribuan koleksi digital tersedia
            </div>
            <div>
                <div class="feature-icon">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </div>
                Riwayat peminjaman terstruktur
            </div>
            <div>
                <div class="feature-icon">
                    <i class="fa-solid fa-user-shield"></i>
                </div>
                Akun aman & terenkripsi
            </div>
        </div>

    </div>

    <!-- ══ RIGHT FORM ══ -->
    <div class="login-side-form">

        <div class="login-card">

            <div class="card-header">
                <div class="card-label">Autentikasi</div>
                <h2>Masuk ke Akun</h2>
                <p class="subtitle">Silakan masukkan kredensial Anda</p>
            </div>

            <?php if($error): ?>
                <div class="error-box">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" autocomplete="off">

                <div class="form-fields">

                    <div class="input-group">
                        <label class="input-label">Username / Email / Identitas</label>
                        <div class="input-wrap">
                            <input type="text"
                                   name="username"
                                   placeholder="Masukkan username, email, atau identitas"
                                   required>
                            <i class="fa-regular fa-user"></i>
                        </div>
                    </div>

                    <div class="input-group">
                        <label class="input-label">Kata Sandi</label>
                        <div class="input-wrap">
                            <input type="password"
                                   name="password"
                                   placeholder="Masukkan kata sandi"
                                   required>
                            <i class="fa-solid fa-lock"></i>
                        </div>
                    </div>

                </div>

                <button type="submit" name="login" class="btn-login">
                    Masuk Sekarang
                </button>

            </form>

            <div class="card-divider"><span>atau</span></div>

            <div class="login-links">
                <a href="forgot_password.php">Lupa kata sandi?</a>
                <div class="divider-dot"></div>
                <a href="register.php">Buat akun baru</a>
            </div>

        </div>

    </div>

</div>

<script src="../assets/js/login-effect.js"></script>

</body>
</html>