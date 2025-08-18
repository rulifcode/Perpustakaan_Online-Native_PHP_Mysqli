<?php
session_start();
include '../config/config.php';


$error = '';

if (isset($_POST['login'])) {
    $input = $_POST['username']; // bisa username/email/identitas
    $password = $_POST['password'];

    // Cek tabel admin berdasarkan username (admin biasanya pakai username)
    $stmtAdmin = $conn->prepare("SELECT * FROM admin WHERE username = ?");
    $stmtAdmin->bind_param("s", $input);
    $stmtAdmin->execute();
    $resultAdmin = $stmtAdmin->get_result();

    if ($resultAdmin->num_rows > 0) {
        $admin = $resultAdmin->fetch_assoc();

        if (password_verify($password, $admin['password'])) {
            $_SESSION['user_id'] = $admin['id'];
            $_SESSION['username'] = $admin['username'];
            $_SESSION['nama'] = $admin['nama'];
            $_SESSION['role'] = 'admin';

            header("Location: ../admin/index.php");
            exit;
        } else {
            $error = "Password salah.";
        }
    } else {
        // Cek tabel users berdasarkan username ATAU email ATAU identitas
        $stmtUser = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ? OR identitas = ?");
        $stmtUser->bind_param("sss", $input, $input, $input);
        $stmtUser->execute();
        $resultUser = $stmtUser->get_result();

        if ($resultUser->num_rows > 0) {
            $user = $resultUser->fetch_assoc();

            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id_user'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['nama'] = $user['username']; // Bisa diganti pakai nama asli jika ada
                $_SESSION['role'] = 'user';

                header("Location: ../user/index.php");
                exit;
            } else {
                $error = "Password salah.";
            }
        } else {
            $error = "Username, email, atau identitas tidak ditemukan.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Masuk - Litera</title>
     <link rel="icon" type="image/png" href="../assets/img/favicon-32x32.png" />
    <link rel="stylesheet" href="../assets/css/login.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <canvas id="stars"></canvas>
    <div class="login-container">
        <div class="login-card">
            <h2>Selamat Datang di <span class="litera">Litera</span></h2>
            <p>Silakan masuk untuk melanjutkan</p>
            
            <?php if ($error): ?>
                <p class="error-msg"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>

            <form method="POST">
                <input type="text" name="username" placeholder="Username / Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit" name="login">Masuk</button>
            </form>
            <!-- Tambahan di bawah tombol “Masuk” -->
                <div class="text-center mt-2">
                    <a href="forgot_password.php" class="text-sm text-blue-600 hover:underline">
                        Lupa kata sandi?
                    </a>
                </div>

                <!-- Tambahan di bawah teks “Belum punya akun?” (agar sejajar estetika) -->
                <p class="mt-3 text-center text-sm">
                    Belum punya akun? <a href="register.php" class="text-blue-600 hover:underline">Daftar di sini</a>
                </p>
        </div>
    </div>

    <script src="../assets/js/login-effect.js"></script>
</body>
</html>

