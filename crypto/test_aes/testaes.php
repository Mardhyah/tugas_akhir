<?php
require_once '../AES.php';

$plaintext = "Data rahasia yang dienkripsi.";
$key = random_bytes(32);
$aes = new AES($key);
$encrypted = $aes->encrypt($plaintext);

file_put_contents("cipher.txt", $encrypted);
file_put_contents("key.bin", $key);
file_put_contents("plain.txt", $plaintext);

echo "Encrypted saved.\n";
