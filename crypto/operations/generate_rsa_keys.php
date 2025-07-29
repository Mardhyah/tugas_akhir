<?php
// Lokasi simpan file kunci
$privateKeyPath = __DIR__ . '/../rsa_keys/private.pem';
$publicKeyPath = __DIR__ . '/../rsa_keys/public.pem';

// Pastikan folder rsa_keys ada
$rsaKeyDir = __DIR__ . '/../rsa_keys';
if (!is_dir($rsaKeyDir)) {
    mkdir($rsaKeyDir, 0755, true);
}

// Konfigurasi OpenSSL
$config = [
    "private_key_bits" => 2048,
    "private_key_type" => OPENSSL_KEYTYPE_RSA,
    "config" => "C:/xampp/php/extras/openssl/openssl.cnf"
];

// Generate kunci RSA
$res = openssl_pkey_new($config);

if (!$res) {
    echo " Gagal membuat kunci RSA.\n";
    while ($msg = openssl_error_string()) {
        echo "OpenSSL Error: $msg\n";
    }
    exit;
}

openssl_pkey_export($res, $privateKey, null, $config);
$keyDetails = openssl_pkey_get_details($res);
$publicKey = $keyDetails['key'];

// Simpan ke file
file_put_contents($privateKeyPath, $privateKey);
file_put_contents($publicKeyPath, $publicKey);

echo "✅ Kunci RSA berhasil dibuat dan disimpan di folder rsa_keys.\n";
