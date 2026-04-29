<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}
echo "<h2>Selamat datang, User</h2>";
echo "<a href='../logout.php'>Logout</a>";
