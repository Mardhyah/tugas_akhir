<?php
include_once __DIR__ . '/../../config/koneksi.php';

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Cek token di database
    $stmt = mysqli_prepare($koneksi, "SELECT id, verify_status FROM user WHERE verify_token = ?");
    mysqli_stmt_bind_param($stmt, "s", $token);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);

        // Cek apakah sudah diverifikasi
        if ($user['verify_status'] == 'verified') {
            echo "<script>
            alert('Akun sudah diverifikasi sebelumnya.');
            window.location='http://localhost/bank_sampah/index.php?page=login';
        </script>";
            exit;
        }

        // Update status menjadi verified
        $update_stmt = mysqli_prepare($koneksi, "UPDATE user SET verify_status = 'verified' WHERE id = ?");
        mysqli_stmt_bind_param($update_stmt, "i", $user['id']);
        mysqli_stmt_execute($update_stmt);

        echo "<script>
        alert('Verifikasi email berhasil! Silakan login.');
        window.location='http://localhost/bank_sampah/index.php?page=login';
    </script>";
        exit;
    } else {
        echo "<script>
        alert('Token tidak valid atau sudah kadaluarsa.');
        window.location='http://localhost/bank_sampah/index.php?page=login';
    </script>";
        exit;
    }
} else {
    echo "<script>alert('Token tidak ditemukan.'); window.location='http://localhost/bank_sampah/index.php?page=login';</script>";
    exit;
}
