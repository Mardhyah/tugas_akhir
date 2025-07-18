<?php
require_once 'crypto_helper.php';

$encryptedValue = 'L2I305MNJ3YnMbfIJeEquMTPDiTQBbydkwXG9adTsbyOt78RNUYrv949w0pJO5bKFCgUo+3rVpNfFz+IPvbLUYk0P20LfIh0JhBIrmpBSns=';

try {
    $decrypted = decryptWithAES($encryptedValue);
    echo "✅ Sukses Dekripsi: $decrypted\n";
} catch (Exception $e) {
    echo "❌ Gagal Dekripsi: " . $e->getMessage();
}
