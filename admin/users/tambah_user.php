<!DOCTYPE html>
<html>
<head>
    <title>Tambah User</title>
</head>
<body>
    <h2>Form Tambah User</h2>
    <form method="POST" action="proses_tambah_user.php">
        <label>Username</label><br>
        <input type="text" name="username" required><br><br>

        <label>Email</label><br>
        <input type="email" name="email" required><br><br>

        <label>Password</label><br>
        <input type="password" name="password" required><br><br>

        <button type="submit">Simpan</button>
    </form>
</body>
</html>