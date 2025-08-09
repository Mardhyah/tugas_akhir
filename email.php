<?php
$db_host = "localhost";
$db_user = "root";
$db_pass = "p@ssKeiCrypto";
$db_name = "banksampah1";

// Koneksi ke database
$koneksi = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

if (!$koneksi) {
    die("Koneksi Gagal:" . mysqli_connect_error());
}

// Ambil semua user
$result = mysqli_query($koneksi, "SELECT id, nama FROM user ORDER BY id ASC");

$emails = []; // untuk menyimpan email sementara agar unik

while ($row = mysqli_fetch_assoc($result)) {
    $id = $row['id'];
    $nama = trim($row['nama']);

    if (empty($nama)) {
        continue;
    }

    // Ambil 2 kata pertama
    $parts = explode(" ", strtolower($nama));
    $kata1 = preg_replace('/[^a-z]/', '', $parts[0]);
    $kata2 = isset($parts[1]) ? preg_replace('/[^a-z]/', '', $parts[1]) : '';

    $baseEmail = $kata1 . $kata2;

    // Pastikan unik
    $email = $baseEmail;
    $suffixChar = 'a';
    while (in_array($email, $emails)) {
        $email = $baseEmail . $suffixChar;
        $suffixChar++;
    }

    $emails[] = $email;

    // Tambahkan domain
    $emailFull = $email . "@gmail.com";

    // Generate token unik
    $token = bin2hex(random_bytes(16));

    // Update ke database
    $stmt = mysqli_prepare(
        $koneksi,
        "UPDATE user 
         SET email = ?, verify_token = ?, verify_status = 'verified', is_verified = 1 
         WHERE id = ?"
    );
    mysqli_stmt_bind_param($stmt, "ssi", $emailFull, $token, $id);
    mysqli_stmt_execute($stmt);
}

echo "Update email, token, dan status verified selesai!";
