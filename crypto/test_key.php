<?php
require_once 'crypto_helper.php';

$encrypted = 'm1b4sOydg4YVOV3hMA8w4/7EzQ+8o9SeHxMW1Rn7LQmxroN0A2GNP9J6oCJ6JwV0cHkjLIidbA7tfQH5gZc7Lg==';
$decrypted = decryptWithAES($encrypted);
echo "HASIL DEKRIPSI: " . $decrypted;
