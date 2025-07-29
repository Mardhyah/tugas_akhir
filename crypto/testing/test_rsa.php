<?php
require_once __DIR__ . '/../core/RSA.php';

use XRsa\XRsa;

// Ambil isi public dan private key dari file
$publicKey = file_get_contents(__DIR__ . '/../rsa_keys/public.pem');
$privateKey = file_get_contents(__DIR__ . '/../rsa_keys/private.pem');

// Inisialisasi objek XRsa
$rsa = new XRsa($publicKey, $privateKey);

// DATA ASLI
$data = "Halo, ini data rahasia!";

// === Test Enkripsi dengan Public Key + Dekripsi dengan Private Key ===
$encrypted = $rsa->publicEncrypt($data);
$decrypted = $rsa->privateDecrypt($encrypted);

echo "=== ENKRIPSI PUBLIC & DEKRIPSI PRIVATE ===\n";
echo "Original: $data\n";
echo "Encrypted: $encrypted\n";
echo "Decrypted: $decrypted\n\n";

// === Test Enkripsi dengan Private Key + Dekripsi dengan Public Key ===
// Biasanya ini dipakai untuk tanda tangan digital
$encrypted2 = $rsa->privateEncrypt($data);
$decrypted2 = $rsa->publicDecrypt($encrypted2);

echo "=== ENKRIPSI PRIVATE & DEKRIPSI PUBLIC ===\n";
echo "Original: $data\n";
echo "Encrypted: $encrypted2\n";
echo "Decrypted: $decrypted2\n";
