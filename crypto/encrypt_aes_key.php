<?php

require_once 'RSA.php'; // class XRsa

use XRsa\XRsa;

// Baca public key
$publicKey = file_get_contents(__DIR__ . '/rsa_keys/public.pem');
if (!$publicKey) {
    die("❌ Gagal membaca public.pem");
}

// Baca AES key dari file dan decode dari base64
$aesKeyBase64 = file_get_contents(__DIR__ . '/original_aes.txt');
if (!$aesKeyBase64) {
    die("❌ Gagal membaca original_aes.txt");
}

$aesKey = base64_decode($aesKeyBase64);
if (!$aesKey) {
    die("❌ Gagal mendecode AES key dari Base64");
}

// Inisialisasi XRsa hanya dengan public key
$rsa = new XRsa($publicKey);

// Enkripsi AES key dengan RSA public key
$encryptedAES = $rsa->publicEncrypt($aesKey);
if (!$encryptedAES) {
    die("❌ Gagal mengenkripsi AES key");
}

// Simpan hasil enkripsi ke file dalam Base64
file_put_contents(__DIR__ . '/aes_encrypted_by_rsa.txt', base64_encode($encryptedAES));

echo "✅ AES key berhasil dienkripsi dengan RSA dan disimpan di 'aes_encrypted_by_rsa.txt'\n";
