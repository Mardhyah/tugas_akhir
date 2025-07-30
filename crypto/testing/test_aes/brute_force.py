import base64
import time
import os
import hashlib
import hmac
from Crypto.Cipher import AES

# Fungsi dekripsi sesuai sistem PHP (AES-256-CBC + IV + HMAC-SHA256)
def decrypt_text(encrypted_b64, key):
    cipher_data = base64.b64decode(encrypted_b64)
    
    if len(key) != 32:
        raise ValueError("Key must be 32 bytes (256-bit)")

    iv_len = 16
    hmac_len = 32

    iv = cipher_data[:iv_len]
    hmac_received = cipher_data[iv_len:iv_len + hmac_len]
    ciphertext = cipher_data[iv_len + hmac_len:]

    # Derive HMAC key the same way as in PHP: HMAC(key, 'integrity')
    hmac_key = hmac.new(key, b'integrity', hashlib.sha256).digest()
    hmac_calculated = hmac.new(hmac_key, iv + ciphertext, hashlib.sha256).digest()

    # Cek integritas
    if not hmac.compare_digest(hmac_received, hmac_calculated):
        raise ValueError("HMAC verification failed")

    # Dekripsi AES-256-CBC
    cipher = AES.new(key, AES.MODE_CBC, iv)
    padded_plaintext = cipher.decrypt(ciphertext)
    plaintext = unpad(padded_plaintext)
    return plaintext

# Fungsi unpad (PKCS7)
def unpad(data):
    pad_len = data[-1]
    if pad_len < 1 or pad_len > 16:
        raise ValueError("Invalid padding")
    return data[:-pad_len]

# Brute force attack
def brute_force(ciphertext_b64, known_key, missing_bytes, original_plaintext, max_time):
    start_time = time.time()
    attempts = 0
    total_possibilities = 2 ** (missing_bytes * 8)

    print(f" Memulai brute force untuk {missing_bytes} byte hilang ({total_possibilities:,} kemungkinan)...")

    for i in range(total_possibilities):
        attempts += 1
        guess = i.to_bytes(missing_bytes, 'big')
        test_key = known_key + guess

        try:
            decrypted = decrypt_text(ciphertext_b64, test_key)
            if decrypted == original_plaintext:
                elapsed = time.time() - start_time
                return test_key, attempts, elapsed
        except Exception:
            continue

        if attempts % 1_000_000 == 0:
            elapsed = time.time() - start_time
            print(f"🔍 {attempts:,} dicoba... Waktu: {elapsed:.2f} detik")
            if elapsed > max_time:
                print(" Waktu habis! Brute force dihentikan.")
                return None, attempts, elapsed

    elapsed = time.time() - start_time
    return None, attempts, elapsed

# =======================
# Load Data dari File
# =======================
with open("cipher.txt", "r") as f:
    ciphertext_b64 = f.read().strip()

with open("key.bin", "rb") as f:
    full_key = f.read()  # Key lengkap 32 byte

with open("plain.txt", "rb") as f:
    original_plaintext = f.read()

# =======================
# Brute Force Simulation
# =======================

time_limits = {1: 60, 2: 180, 3: 600}  # contoh: 1 byte (1 menit), 2 byte (3 menit), dst.

for missing_bytes in [1, 2, 3]:
    print(f"\nUji brute force dengan {missing_bytes} byte kunci hilang...")
    partial_key = full_key[:-missing_bytes]
    max_time = time_limits[missing_bytes]

    recovered_key, attempts, elapsed = brute_force(ciphertext_b64, partial_key, missing_bytes, original_plaintext, max_time)

    if recovered_key:
        print(f"Kunci berhasil ditemukan: {recovered_key.hex()}")
        print(f"Total percobaan: {attempts:,}, Waktu: {elapsed:.2f} detik")
    else:
        print(f"Gagal mendapatkan kunci setelah {attempts:,} percobaan (dalam {elapsed:.2f} detik)")
