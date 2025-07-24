<?php
require_once __DIR__ . '/../config/koneksi_cryptostore.php';

// Baca AES key terenkripsi dari file
$encryptedAESBase64 = file_get_contents(__DIR__ . '/../data//aes_encrypted_by_rsa.txt');
if (!$encryptedAESBase64) {
    die("❌ Gagal membaca file aes_encrypted_by_rsa.txt");
}

// Pastikan key berbentuk string base64 yang valid
$encryptedAESBase64 = trim($encryptedAESBase64); // hapus whitespace
if (base64_decode($encryptedAESBase64, true) === false) {
    die("❌ Encrypted AES key bukan base64 valid.");
}

// Nonaktifkan key lama (opsional: bisa tambahkan log)
$update = mysqli_query($conn, "UPDATE encrypted_aes_keys SET is_active = 0 WHERE is_active = 1");
if (!$update) {
    die("❌ Gagal menonaktifkan key lama: " . mysqli_error($conn));
}

// Simpan key baru ke database
$stmt = $conn->prepare("INSERT INTO encrypted_aes_keys (encrypted_aes_key, is_active) VALUES (?, 1)");
if (!$stmt) {
    die("❌ Prepare statement gagal: " . $conn->error);
}

$stmt->bind_param("s", $encryptedAESBase64);

if ($stmt->execute()) {
    echo "✅ AES key terenkripsi berhasil disimpan ke database.\n";
} else {
    echo "❌ Gagal menyimpan ke database: " . $stmt->error;
}

$stmt->close();
$conn->close();
