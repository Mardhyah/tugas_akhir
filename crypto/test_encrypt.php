<?php
require_once __DIR__ . '/crypto_helper.php';

echo "🧪 Memulai pengujian enkripsi dan dekripsi dengan AES dari database...\n";

try {
    // 1. Teks asli
    $originalText = "Ini adalah pesan rahasia dari crypto_helper.";
    echo "Original Text: $originalText\n";

    // 2. Enkripsi
    $encrypted = encryptWithAES($originalText);
    echo "Encrypted (base64): $encrypted\n";

    // 3. Dekripsi
    $decrypted = decryptWithAES($encrypted);
    echo "Decrypted Text: $decrypted\n";

    // 4. Validasi
    if ($originalText === $decrypted) {
        echo "✅ Test berhasil: teks asli dan hasil dekripsi sama.\n";
    } else {
        echo "❌ Test gagal: hasil dekripsi tidak sama dengan teks asli.\n";
    }
} catch (Exception $e) {
    echo "❌ Terjadi error saat pengujian: " . $e->getMessage() . "\n";
}
