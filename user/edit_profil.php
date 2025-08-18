<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

include '../config/config.php';

$user_id = $_SESSION['user_id'];

// Ambil data user
$query = mysqli_query($conn, "SELECT username, email, alamat FROM users WHERE id_user = '$user_id'");
$user = mysqli_fetch_assoc($query);

// Proses update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);

    mysqli_query($conn, "UPDATE users SET alamat = '$alamat' WHERE id_user = '$user_id'");
    echo "<script>alert('Profil berhasil diperbarui.'); window.location.href = 'index.php';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Profil</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <h2>Edit Profil</h2>
    <form method="POST">
        <p><strong>Username:</strong> <?= htmlspecialchars($user['username']) ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>

        <label for="alamat">Alamat:</label><br>
        <textarea name="alamat" rows="4" cols="40" required><?= htmlspecialchars($user['alamat']) ?></textarea><br><br>

        <button type="submit">Simpan</button>
        <a href="index.php">Batal</a>
    </form>
</body>
</html>