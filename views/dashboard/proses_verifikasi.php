<?php
include_once __DIR__ . '/../config/koneksi.php';

if (isset($_GET['id']) && isset($_GET['aksi'])) {
    $id = intval($_GET['id']);
    $aksi = $_GET['aksi'];

    if ($aksi === 'acc') {
        $query = "UPDATE user SET is_verified = 1 WHERE id = $id";
    } elseif ($aksi === 'tolak') {
        $query = "DELETE FROM user WHERE id = $id";
    } else {
        echo "<script>alert('Aksi tidak valid.'); window.location='index.php?page=notifikasi_nasabah';</script>";
        exit;
    }

    if (mysqli_query($koneksi, $query)) {
        echo "<script>alert('Aksi berhasil dijalankan.'); window.location='index.php?page=notifikasi_nasabah';</script>";
    } else {
        echo "<script>alert('Terjadi kesalahan saat memproses.'); window.location='index.php?page=notifikasi_nasabah';</script>";
    }
} else {
    echo "<script>alert('Data tidak valid.'); window.location='index.php?page=notifikasi_nasabah';</script>";
}
