<?php

namespace App\Service;

class TokenEncryptor
{
    private const CIPHER = 'aes-256-cbc';

    public function __construct(
        private readonly string $encryptionKeyBase64,
    ) {
    }

    public function encrypt(string $plaintext): string
    {
        $key = $this->getKey();
        $iv = random_bytes(openssl_cipher_iv_length(self::CIPHER));
        $encrypted = openssl_encrypt($plaintext, self::CIPHER, $key, OPENSSL_RAW_DATA, $iv);

        if ($encrypted === false) {
            throw new \RuntimeException('Encryption failed');
        }

        return base64_encode($iv.$encrypted);
    }

    public function decrypt(string $ciphertext): string
    {
        $key = $this->getKey();
        $raw = base64_decode($ciphertext, true);
        if ($raw === false) {
            throw new \RuntimeException('Invalid ciphertext');
        }

        $ivLength = openssl_cipher_iv_length(self::CIPHER);
        $iv = substr($raw, 0, $ivLength);
        $encrypted = substr($raw, $ivLength);
        $decrypted = openssl_decrypt($encrypted, self::CIPHER, $key, OPENSSL_RAW_DATA, $iv);

        if ($decrypted === false) {
            throw new \RuntimeException('Decryption failed');
        }

        return $decrypted;
    }

    private function getKey(): string
    {
        $key = base64_decode($this->encryptionKeyBase64, true);
        if ($key === false || strlen($key) < 32) {
            $key = hash('sha256', $this->encryptionKeyBase64, true);
        }

        return substr($key, 0, 32);
    }
}
