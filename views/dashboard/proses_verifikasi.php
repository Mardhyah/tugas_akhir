<?php
include_once __DIR__ . '/../config/koneksi.php';
include_once __DIR__ . '/../config/mail/send_verified_success.php';

if (isset($_GET['id']) && isset($_GET['aksi'])) {
    $id = intval($_GET['id']);
    $aksi = $_GET['aksi'];

    // Ambil email user
    $result = mysqli_query($koneksi, "SELECT email FROM user WHERE id = $id");
    $user = mysqli_fetch_assoc($result);
    $email = $user['email'] ?? null;

    if ($aksi === 'acc') {
        $query = "UPDATE user SET is_verified = 1 WHERE id = $id";
    } elseif ($aksi === 'tolak') {
        $query = "DELETE FROM user WHERE id = $id";
    } else {
        echo "<script>alert('Aksi tidak valid.'); window.location='index.php?page=notifikasi_nasabah';</script>";
        exit;
    }

    if (mysqli_query($koneksi, $query)) {
        if ($aksi === 'acc' && $email) {
            sendmail_verified_success($email); // Kirim email verifikasi sukses
        }

        echo "<script>alert('Aksi berhasil dijalankan.'); window.location='index.php?page=notifikasi_nasabah';</script>";
    } else {
        echo "<script>alert('Terjadi kesalahan saat memproses.'); window.location='index.php?page=notifikasi_nasabah';</script>";
    }
} else {
    echo "<script>alert('Data tidak valid.'); window.location='index.php?page=notifikasi_nasabah';</script>";
}
