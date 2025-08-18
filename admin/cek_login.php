<?php
// admin/cek_login.php
session_start();
include('../config/config.php'); // Koneksi ke database

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Query untuk mengecek username dan password admin
    $query = "SELECT * FROM admin WHERE username = ? LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $admin = $result->fetch_assoc();

    if ($admin && password_verify($password, $admin['password'])) {
        // Jika login berhasil, set session
        $_SESSION['admin'] = $admin['username'];
        header("Location: index.php"); // Arahkan ke dashboard admin
        exit;
    } else {
        // Jika login gagal, tampilkan pesan error
        echo "Username atau Password salah!";
    }
}
?>