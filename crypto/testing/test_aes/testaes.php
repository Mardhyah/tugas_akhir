<?php
require_once __DIR__ . '/../../core/AES.php';

// Baca key dari file (Base64), decode ke biner
$keyBase64 = trim(file_get_contents(__DIR__ . '/../../data/original_aes.txt'));
$key = base64_decode($keyBase64);

// Pastikan key panjangnya benar
if ($key === false || strlen($key) !== 32) {
    die("Kunci tidak valid. Harus 32 byte (256-bit).");
}

// Siapkan plaintext
$plaintext = "Data rahasia yang dienkripsi.";

// Inisialisasi AES dengan key tersebut
$aes = new AES($key);

// Enkripsi
$encrypted = $aes->encrypt($plaintext);

// Simpan hasil
file_put_contents("cipher.txt", $encrypted);
file_put_contents("key.bin", $key);          // backup key biner
file_put_contents("plain.txt", $plaintext);

echo "✅ Encrypted saved using key from original_aes.txt.\n";
