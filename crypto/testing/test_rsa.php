<?php
require_once __DIR__ . '/../core/RSA.php';

use XRsa\XRsa;

$keys = XRsa::createKeys(); // generate baru

$rsa = new XRsa($keys['public_key'], $keys['private_key']);

$aesKey = "ini_kunci_AES_yang_rahasia";
$encrypted = $rsa->publicEncrypt($aesKey);
$decrypted = $rsa->privateDecrypt($encrypted);

echo "Asli: $aesKey\n";
echo "Terenkripsi: $encrypted\n";
echo "Didekripsi: $decrypted\n";
