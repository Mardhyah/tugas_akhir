<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Gunakan path absolut supaya koneksi pasti ketemu
require_once __DIR__ . '/../../config/koneksi.php';

if (!isset($koneksi)) {
    die("Variabel \$conn tidak terdefinisi. Pastikan koneksi database berhasil.");
}

if (!isset($_SESSION['username'])) {
    header('Location: index.php?page=login');
    exit();
}

$username = $_SESSION['username'];
$sql = "SELECT nama FROM user WHERE username = ?";
$stmt = mysqli_prepare($koneksi, $sql);
if ($stmt) {
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $nama);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);
} else {
    echo "Terjadi kesalahan pada query: " . mysqli_error($conn);
}
