<?php

/**
 * AES Secure Encryption Class (AES-256-CBC + IV + HMAC + PKCS7 Padding)
 * Aman digunakan untuk sistem nyata.
 *
 * Fitur:
 * - Menggunakan AES-256-CBC (mode CBC)
 * - IV acak disisipkan bersama ciphertext
 * - HMAC-SHA256 menjamin integritas data
 * - Padding otomatis dari OpenSSL
 * - Base64 encoding untuk penyimpanan/transmisi
 */
class AES
{
    private string $key;
    private string $hmacKey;
    private string $cipherMethod = 'AES-256-CBC';
    private int $ivLength;

    /**
     * Konstruktor: Inisialisasi kunci utama dan derive kunci HMAC
     *
     * @param string $key 32-byte (256-bit) kunci utama
     * @throws Exception jika kunci tidak valid
     */
    public function __construct(string $key)
    {
        if (strlen($key) !== 32) {
            throw new Exception("Key must be exactly 32 bytes (256-bit)");
        }
        $this->key = $key;
        $this->hmacKey = hash_hmac('sha256', $key, 'integrity', true); // derive HMAC key
        $this->ivLength = openssl_cipher_iv_length($this->cipherMethod);
    }

    /**
     * Enkripsi plaintext menggunakan AES-256-CBC + IV + HMAC
     *
     * Format hasil: Base64(IV + HMAC + ciphertext)
     *
     * @param string $plaintext
     * @return string
     * @throws Exception
     */
    public function encrypt(string $plaintext): string
    {
        $iv = openssl_random_pseudo_bytes($this->ivLength);

        $ciphertext = openssl_encrypt(
            $plaintext,
            $this->cipherMethod,
            $this->key,
            OPENSSL_RAW_DATA,
            $iv
        );

        if ($ciphertext === false) {
            throw new Exception("Encryption failed.");
        }

        // Buat HMAC: menjamin integritas data (iv + ciphertext)
        $hmac = hash_hmac('sha256', $iv . $ciphertext, $this->hmacKey, true);

        // Gabungkan semua: IV + HMAC + ciphertext
        $encryptedData = $iv . $hmac . $ciphertext;

        // Encode agar bisa disimpan/dikirim sebagai teks
        return base64_encode($encryptedData);
    }

    /**
     * Dekripsi hasil dari fungsi encrypt()
     *
     * @param string $encoded Base64(IV + HMAC + ciphertext)
     * @return string plaintext
     * @throws Exception jika format salah atau integritas gagal
     */
    public function decrypt(string $encoded): string
    {
        $decoded = base64_decode($encoded, true);

        if ($decoded === false || strlen($decoded) < $this->ivLength + 32) {
            throw new Exception("Invalid encrypted data format.");
        }

        $iv = substr($decoded, 0, $this->ivLength);
        $hmac = substr($decoded, $this->ivLength, 32);
        $ciphertext = substr($decoded, $this->ivLength + 32);

        // Verifikasi integritas data
        $calculatedHmac = hash_hmac('sha256', $iv . $ciphertext, $this->hmacKey, true);
        if (!hash_equals($hmac, $calculatedHmac)) {
            throw new Exception("HMAC verification failed. Data may be tampered.");
        }

        $plaintext = openssl_decrypt(
            $ciphertext,
            $this->cipherMethod,
            $this->key,
            OPENSSL_RAW_DATA,
            $iv
        );

        if ($plaintext === false) {
            throw new Exception("Decryption failed.");
        }

        return $plaintext;
    }
}
