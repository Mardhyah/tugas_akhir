<?php

namespace XRsa;

class XRsa
{
    const CHAR_SET = "UTF-8";
    const BASE_64_FORMAT = "UrlSafeNoPadding";
    const RSA_ALGORITHM_KEY_TYPE = OPENSSL_KEYTYPE_RSA;

    protected $public_key;
    protected $private_key;
    protected $key_len;

    public function __construct($pub_key = null, $pri_key = null)
    {
        $this->public_key = $pub_key;
        $this->private_key = $pri_key;

        if ($this->public_key) {
            $pub_id = openssl_pkey_get_public($this->public_key);
            if (!$pub_id) {
                throw new \Exception("Public key tidak valid atau gagal dibaca.");
            }
            $this->key_len = openssl_pkey_get_details($pub_id)['bits'];
        } elseif ($this->private_key) {
            $pri_id = openssl_pkey_get_private($this->private_key);
            if (!$pri_id) {
                throw new \Exception("Private key tidak valid atau gagal dibaca.");
            }
            $this->key_len = openssl_pkey_get_details($pri_id)['bits'];
        } else {
            throw new \Exception("Minimal salah satu dari public atau private key harus disediakan.");
        }
    }

    public function publicEncrypt($data)
    {
        $encrypted = '';
        $part_len = $this->key_len / 8 - 11;
        $parts = str_split($data, $part_len);

        foreach ($parts as $part) {
            $encrypted_temp = '';
            openssl_public_encrypt($part, $encrypted_temp, $this->public_key);
            $encrypted .= $encrypted_temp;
        }

        return url_safe_base64_encode($encrypted);
    }

    public function privateDecrypt($encrypted)
    {
        $decrypted = "";
        $part_len = $this->key_len / 8;
        $base64_decoded = url_safe_base64_decode($encrypted);
        $parts = str_split($base64_decoded, $part_len);

        foreach ($parts as $part) {
            $decrypted_temp = '';
            openssl_private_decrypt($part, $decrypted_temp, $this->private_key);
            $decrypted .= $decrypted_temp;
        }
        return $decrypted;
    }

    public function privateEncrypt($data)
    {
        $encrypted = '';
        $part_len = $this->key_len / 8 - 11;
        $parts = str_split($data, $part_len);

        foreach ($parts as $part) {
            $encrypted_temp = '';
            openssl_private_encrypt($part, $encrypted_temp, $this->private_key);
            $encrypted .= $encrypted_temp;
        }

        return url_safe_base64_encode($encrypted);
    }

    public function publicDecrypt($encrypted)
    {
        $decrypted = "";
        $part_len = $this->key_len / 8;
        $base64_decoded = url_safe_base64_decode($encrypted);
        $parts = str_split($base64_decoded, $part_len);

        foreach ($parts as $part) {
            $decrypted_temp = '';
            openssl_public_decrypt($part, $decrypted_temp, $this->public_key);
            $decrypted .= $decrypted_temp;
        }
        return $decrypted;
    }
}

// Fungsi pembantu Base64 URL Safe
if (! function_exists('url_safe_base64_encode')) {
    function url_safe_base64_encode($data)
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
    }
}

if (! function_exists('url_safe_base64_decode')) {
    function url_safe_base64_decode($data)
    {
        $base_64 = str_replace(['-', '_'], ['+', '/'], $data);
        $remainder = strlen($base_64) % 4;
        if ($remainder) {
            $base_64 .= str_repeat('=', 4 - $remainder);
        }
        return base64_decode($base_64);
    }
}
