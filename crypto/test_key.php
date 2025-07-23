<?php
require_once 'crypto_helper.php';

$encrypted = 'DZB+KlKpcdS4zyK0wtZSqS32x1FlPeGsmn09PiqDDLUK0kSyVUKnHAMhB5SBoZndg6Xpx09FzVT4psFoyjVqLQ==';
$decrypted = decryptWithAES($encrypted);
echo "HASIL DEKRIPSI: " . $decrypted;

$jumlah = "0.5";
$data_terenkripsi = encryptWithAES("EMAS:" . $jumlah);  // hasil: EMAS:0.5
