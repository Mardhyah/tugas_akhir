<?php
// Buat AES key 256-bit (32 bytes)
$randomAESKey = openssl_random_pseudo_bytes(32);

if ($randomAESKey === false) {
    die("❌ Gagal menghasilkan AES key.");
}

// Simpan ke file original_aes.txt dalam format base64 agar mudah disimpan dan dibaca

$aesKeyBase64 = base64_encode($randomAESKey);

file_put_contents(__DIR__ . '/../data/original_aes.txt', $aesKeyBase64);

echo "✅ AES Key berhasil dibuat dan disimpan ke original_aes.txt\n";
