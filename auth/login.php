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

<title>Masuk - Litera</title>

<link rel="icon" type="image/png" href="../assets/img/favicon-32x32.png">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet">

<link rel="stylesheet" href="../assets/css/login.css">
</head>

<body>

<canvas id="stars"></canvas>

<div class="login-wrapper">

    <!-- LEFT SIDE -->
    <div class="login-side-brand">

        <a href="../index.php" class="brand-logo">
            <img src="../assets/img/logoTr.png" alt="Litera">
            <div>
                <span class="brand-name">Litera</span>
                <span class="brand-sub">Digital Library Experience</span>
            </div>
        </a>

        <h1>Masuk dan lanjutkan perjalanan membaca.</h1>

        <p>
            Akses koleksi digital, riwayat peminjaman,
            serta pengalaman perpustakaan modern dalam satu platform.
        </p>

        <div class="brand-feature-list">
            <div><i class="fa-solid fa-book-open"></i> Ribuan koleksi digital</div>
            <div><i class="fa-solid fa-clock-rotate-left"></i> Riwayat peminjaman</div>
            <div><i class="fa-solid fa-user-shield"></i> Aman & cepat</div>
        </div>

    </div>

    <!-- RIGHT SIDE -->
    <div class="login-side-form">

        <div class="login-card">

            <h2>Masuk ke Akun</h2>
            <p class="subtitle">Silakan login untuk melanjutkan</p>

            <?php if($error): ?>
                <div class="error-box">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" autocomplete="off">

                <div class="input-group">
                    <i class="fa-regular fa-user"></i>
                    <input type="text"
                           name="username"
                           placeholder="Username / Email / Identitas"
                           required>
                </div>

                <div class="input-group">
                    <i class="fa-solid fa-lock"></i>
                    <input type="password"
                           name="password"
                           placeholder="Password"
                           required>
                </div>

                <button type="submit" name="login" class="btn-login">
                    Masuk Sekarang
                </button>

            </form>

            <div class="login-links">

                <a href="forgot_password.php">
                    Lupa kata sandi?
                </a>

                <span>•</span>

                <a href="register.php">
                    Buat akun baru
                </a>

            </div>

        </div>

    </div>

</div>

<script src="../assets/js/login-effect.js"></script>

</body>
</html>