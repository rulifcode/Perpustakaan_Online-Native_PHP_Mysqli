<?php
include '../config/config.php'; // ubah path jika perlu

$username = $_POST['username'];
$email = $_POST['email'];
$password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Enkripsi

$query = "INSERT INTO users (username, email, password) 
          VALUES ('$username', '$email', '$password')";

if (mysqli_query($conn, $query)) {
    echo "User berhasil ditambahkan. <a href='users.php'>Kembali</a>";
} else {
    echo "Gagal menambah user: " . mysqli_error($conn);
}
?>