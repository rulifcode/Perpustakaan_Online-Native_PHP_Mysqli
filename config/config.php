<?php
$host = "localhost";
$user = "root";
$pass = "";
$db = "perpustakaan";

$conn = mysqli_connect("localhost", "root", "", "perpustakaan");

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>