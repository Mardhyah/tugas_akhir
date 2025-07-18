<?php
require_once __DIR__ . '/../config/koneksi_cryptostore.php';
require_once __DIR__ . '/RSA.php';  // Pastikan ini adalah class XRsa
require_once __DIR__ . '/AES.php';          // File AES yang kamu punya

use XRsa\XRsa;

/**
 * Helper untuk mengambil AES key dari database (terenkripsi), dekripsi dengan RSA, dan kembalikan objek AES siap pakai.
 *
 * @return AES
 * @throws Exception
 */
function getAesInstanceFromDatabase(): AES
{
    global $conn;

    // Ambil 1 AES terenkripsi yang aktif
    $query = "SELECT encrypted_aes_key FROM encrypted_aes_keys WHERE is_active = 1 LIMIT 1";
    $result = mysqli_query($conn, $query);

    if (!$result || mysqli_num_rows($result) === 0) {
        throw new Exception("❌ Tidak ada AES key aktif ditemukan di database.");
    }

    $row = mysqli_fetch_assoc($result);
    $encryptedAESBase64 = $row['encrypted_aes_key'];

    // Decode base64
    $encryptedAESBinary = base64_decode($encryptedAESBase64);
    if ($encryptedAESBinary === false) {
        throw new Exception("❌ Gagal decode AES terenkripsi dari base64.");
    }

    // Ambil RSA private key
    $privateKeyPath = __DIR__ . '/rsa_keys/private.pem';
    $privateKey = file_get_contents($privateKeyPath);
    if (!$privateKey) {
        throw new Exception("❌ Gagal membaca RSA private key dari $privateKeyPath");
    }

    // Dekripsi AES dengan RSA
    $rsa = new XRsa(null, $privateKey);
    $aesKey = $rsa->privateDecrypt($encryptedAESBinary);
    if (!$aesKey || strlen($aesKey) !== 32) {
        throw new Exception("❌ Gagal mendekripsi AES key atau panjang tidak valid.");
    }

    // Inisialisasi AES class dengan kunci hasil dekripsi
    return new AES($aesKey);
}

/**
 * Enkripsi data menggunakan AES key yang didekripsi dari RSA
 *
 * @param string $plaintext
 * @return string
 * @throws Exception
 */
function encryptWithAES(string $plaintext): string
{
    $aes = getAesInstanceFromDatabase();
    return $aes->encrypt($plaintext);
}

/**
 * Dekripsi data terenkripsi menggunakan AES key dari RSA
 *
 * @param string $encryptedData
 * @return string
 * @throws Exception
 */
function decryptWithAES(string $encryptedData): string
{
    $aes = getAesInstanceFromDatabase();
    return $aes->decrypt($encryptedData);
}

function safeDecrypt($value)
{
    try {
        return decryptWithAES($value);
    } catch (Exception $e) {
        return null;
    }
}
