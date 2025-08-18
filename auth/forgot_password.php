<?php 
session_start(); 
require_once '../config/config.php';
require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);

    $stmt = $conn->prepare("SELECT id_user, username FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows) {
        $user = $res->fetch_assoc();
        $token  = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $stmt = $conn->prepare("UPDATE users SET reset_token=?, reset_expires=? WHERE id_user=?");
        $stmt->bind_param("ssi", $token, $expiry, $user['id_user']);
        $stmt->execute();

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.example.com'; // Ganti dengan hosting atau smtp.gmail.com
            $mail->SMTPAuth   = true;
            $mail->Username   = 'no-reply@example.com';
            $mail->Password   = 'PASSWORDMU';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('no-reply@example.com', 'Litera');
            $mail->addAddress($email, $user['username']);

            $mail->isHTML(true);
            $mail->Subject = 'Reset Kata Sandi Litera';

            $link = "http://localhost/perpustakaan_app/auth/reset_password.php?token=$token";
            $mail->Body    = "
                Hai {$user['username']},<br><br>
                Klik tautan berikut untuk mereset kata sandi Anda (berlaku 1 jam):<br>
                <a href='$link'>Reset Kata Sandi</a><br><br>
                Jika Anda tidak meminta reset, abaikan email ini.
            ";

            $mail->send();
            $success = "Instruksi reset sudah dikirim ke email Anda.";
        } catch (Exception $e) {
            $error = "Gagal mengirim email. Error: {$mail->ErrorInfo}";
        }
    } else {
        $error = "Email tidak terdaftar.";
    }
}
?>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Lupa Kata Sandi - Litera</title>
    
    <link rel="icon" type="image/png" href="../assets/img/favicon-32x32.png" />
    <link rel="stylesheet" href="../assets/css/login.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <canvas id="stars"></canvas>
    <div class="login-container">
        <div class="login-card">
            <h2>Lupa Kata Sandi</h2>
            <p>Masukkan email yang terdaftar, kami akan mengirimkan tautan reset</p>
            
            <?php if ($error): ?>
                <p class="error-msg"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <p class="success-msg"><?= htmlspecialchars($success) ?></p>
            <?php endif; ?>
            
            <form method="POST" autocomplete="off">
                <input type="email" name="email" placeholder="Email" required>
                <button type="submit">Kirim Tautan Reset</button>
            </form>
            
            <!-- Link kembali ke login -->
            <div class="text-center mt-2">
                <a href="login.php" class="text-sm text-blue-600 hover:underline">
                    <i class="fas fa-arrow-left"></i> Kembali ke halaman masuk
                </a>
            </div>
            
            <!-- Link daftar jika belum punya akun -->
            <p class="mt-3 text-center text-sm">
                Belum punya akun? <a href="register.php" class="text-blue-600 hover:underline">Daftar di sini</a>
            </p>
        </div>
    </div>
    
    <script src="../assets/js/login-effect.js"></script>
</body>
</html>