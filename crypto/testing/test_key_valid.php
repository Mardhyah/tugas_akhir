<?php
$publicKey = file_get_contents(__DIR__ . '/../rsa_keys/public.pem');
if (!$publicKey) {
    echo "❌ Gagal baca public key\n";
    exit;
}

$keyRes = openssl_get_publickey($publicKey);
if ($keyRes === false) {
    echo "❌ Public key tidak valid\n";
} else {
    echo "✅ Public key valid\n";
}
