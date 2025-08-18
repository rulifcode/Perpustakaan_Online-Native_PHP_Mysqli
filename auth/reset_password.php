<?php
require_once '../config/config.php';

$error = '';
$success = '';

$token = $_GET['token'] ?? '';
if (!$token) { die('Token tidak valid.'); }

// Cek token
$stmt = $conn->prepare("SELECT id_user, reset_expires FROM users WHERE reset_token=?");
$stmt->bind_param("s", $token);
$stmt->execute();
$res = $stmt->get_result();

if (!$res->num_rows) { die('Token tidak ditemukan.'); }

$user = $res->fetch_assoc();
if (new DateTime() > new DateTime($user['reset_expires'])) {
    die('Token kedaluwarsa. Silakan minta reset baru.');
}

// Proses reset password
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pw1 = $_POST['password'];
    $pw2 = $_POST['confirm_password'];

    if ($pw1 !== $pw2) {
        $error = 'Konfirmasi kata sandi tidak sama.';
    } elseif (strlen($pw1) < 6) {
        $error = 'Kata sandi minimal 6 karakter.';
    } else {
        $hash = password_hash($pw1, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password=?, reset_token=NULL, reset_expires=NULL WHERE id_user=?");
        $stmt->bind_param("si", $hash, $user['id_user']);
        $stmt->execute();
        $success = "Kata sandi berhasil diperbarui. <a href='login.php'>Login</a>";
    }
}
?>

<!-- Tampilan Form -->
<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
</head>
<body>
    <h2>Reset Kata Sandi</h2>

    <?php if ($error): ?>
        <p style="color:red"><?= $error ?></p>
    <?php endif; ?>

    <?php if ($success): ?>
        <p style="color:green"><?= $success ?></p>
    <?php else: ?>
    <form method="POST">
        <label>Password Baru:</label><br>
        <input type="password" name="password" required><br><br>

        <label>Konfirmasi Password:</label><br>
        <input type="password" name="confirm_password" required><br><br>

        <button type="submit">Reset Password</button>
    </form>
    <?php endif; ?>
</body>
</html>

<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"><title>Reset Kata Sandi</title></head>
<body>
<h2>Reset Kata Sandi</h2>
<?php if (isset($error))  echo "<p style='color:red'>$error</p>"; ?>
<?php if (isset($success)) { echo "<p style='color:green'>$success</p>"; exit; } ?>

<form method="post" autocomplete="off">
    <input type="password" name="password" placeholder="Kata sandi baru" required>
    <input type="password" name="confirm_password" placeholder="Ulangi kata sandi" required>
    <button type="submit">Perbarui Kata Sandi</button>
</form>
</body>
</html>
