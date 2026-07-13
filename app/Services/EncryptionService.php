<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\AppException;

/**
 * Encryption Service
 *
 * AES-256-GCM authenticated encryption for sensitive PII fields.
 * Key stored in .env as APP_ENCRYPTION_KEY (64-character hex string = 32 bytes).
 *
 * Fields encrypted: national_id, address, internal_notes, investigation_notes.
 * Passwords are NEVER encrypted here — they are hashed with Argon2ID.
 *
 * Output format: base64(iv[12] + ciphertext + tag[16])
 */
final class EncryptionService
{
    private const CIPHER     = 'aes-256-gcm';
    private const IV_LENGTH  = 12;  // GCM recommended IV size
    private const TAG_LENGTH = 16;

    private readonly string $key;

    /**
     * @throws AppException If the encryption key is missing or invalid.
     */
    public function __construct()
    {
        $hexKey = $_ENV['APP_ENCRYPTION_KEY'] ?? '';

        if (strlen($hexKey) !== 64) {
            throw new AppException(
                'APP_ENCRYPTION_KEY must be a 64-character hex string (32 bytes). '
                . 'Generate with: php -r "echo bin2hex(random_bytes(32));"'
            );
        }

        $this->key = hex2bin($hexKey);
    }

    /**
     * Encrypts plaintext using AES-256-GCM.
     *
     * @throws AppException On encryption failure.
     */
    public function encrypt(string $plaintext): string
    {
        if ($plaintext === '') {
            return '';
        }

        $iv = random_bytes(self::IV_LENGTH);

        $ciphertext = openssl_encrypt(
            $plaintext,
            self::CIPHER,
            $this->key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag,
            '',
            self::TAG_LENGTH
        );

        if ($ciphertext === false) {
            throw new AppException('Encryption failed.');
        }

        // Store iv + ciphertext + tag together, base64-encoded
        return base64_encode($iv . $ciphertext . $tag);
    }

    /**
     * Decrypts a value produced by encrypt().
     *
     * @throws AppException On decryption failure (tampered data, wrong key, etc.).
     */
    public function decrypt(string $encoded): string
    {
        if ($encoded === '') {
            return '';
        }

        $raw = base64_decode($encoded, true);

        if ($raw === false || strlen($raw) < self::IV_LENGTH + self::TAG_LENGTH + 1) {
            throw new AppException('Decryption failed: invalid ciphertext format.');
        }

        $iv         = substr($raw, 0, self::IV_LENGTH);
        $tag        = substr($raw, -self::TAG_LENGTH);
        $ciphertext = substr($raw, self::IV_LENGTH, -self::TAG_LENGTH);

        $plaintext = openssl_decrypt(
            $ciphertext,
            self::CIPHER,
            $this->key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );

        if ($plaintext === false) {
            throw new AppException('Decryption failed: authentication tag mismatch or corrupt data.');
        }

        return $plaintext;
    }

    /**
     * Returns true if the value appears to be encrypted by this service.
     */
    public function isEncrypted(string $value): bool
    {
        $decoded = base64_decode($value, true);
        return $decoded !== false
            && strlen($decoded) >= self::IV_LENGTH + self::TAG_LENGTH + 1;
    }
}
