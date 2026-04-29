<?php
include '../config/config.php';

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    $stmt = $conn->prepare("SELECT * FROM users WHERE verify_token=? AND is_verified=0");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $update = $conn->prepare("UPDATE users SET is_verified=1, verify_token=NULL WHERE verify_token=?");
        $update->bind_param("s", $token);
        $update->execute();
        echo "✅ Verifikasi berhasil. Silakan login.";
    } else {
        echo "❌ Token tidak valid atau akun sudah diverifikasi.";
    }
}
?>
