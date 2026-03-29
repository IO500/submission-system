<?php
declare(strict_types=1);

namespace App\Database\Type;

use Cake\Core\Configure;
use Cake\Database\DriverInterface;
use Cake\Database\Type\BaseType;
use Cake\Database\Type\OptionalConvertInterface;
use Cake\Utility\Security;
use PDO;

/**
 * Database type that transparently encrypts values on write and decrypts on read.
 *
 * Uses AES-256-CBC via CakePHP Security::encrypt/decrypt.
 * Ciphertext is base64-encoded before storage.
 *
 * Detection heuristic (is a value already ciphertext?):
 *   - length ≥ 128 chars (minimum base64 of 96-byte raw output)
 *   - passes strict base64_decode
 * Real names/emails are far shorter than 128 chars, so false positives
 * are not a practical concern.
 */
class EncryptedType extends BaseType implements OptionalConvertInterface
{
    /**
     * Minimum length of a base64-encoded Security::encrypt() ciphertext.
     * Raw = 64 B hex-HMAC + 16 B IV + ≥16 B AES block = 96 B → 128 base64 chars.
     */
    private const MIN_CIPHER_LEN = 128;

    private function encKey(): string
    {
        return (string)Configure::read('App.emailEncryptionKey');
    }

    private function looksEncrypted(string $value): bool
    {
        return strlen($value) >= self::MIN_CIPHER_LEN
            && base64_decode($value, true) !== false;
    }

    // -------------------------------------------------------------------------
    // Interface methods
    // -------------------------------------------------------------------------

    /**
     * Decrypt when reading from the database.
     */
    public function toPHP($value, DriverInterface $driver): ?string
    {
        if ($value === null || $value === '') {
            return $value === null ? null : '';
        }

        $str = (string)$value;

        if (!$this->looksEncrypted($str)) {
            // Plain text (not yet migrated, or never encrypted).
            return $str;
        }

        $decoded = base64_decode($str, true);
        if ($decoded === false) {
            return $str;
        }

        $plain = Security::decrypt($decoded, $this->encKey());

        // If decryption fails (wrong key, truncated data, corruption) return
        // null rather than exposing raw ciphertext to the view layer.
        return ($plain !== false && $plain !== null) ? $plain : null;
    }

    /**
     * Encrypt when writing to the database.
     */
    public function toDatabase($value, DriverInterface $driver): ?string
    {
        if ($value === null || $value === '') {
            return $value === null ? null : '';
        }

        $str = (string)$value;

        if ($this->looksEncrypted($str)) {
            // Already ciphertext — do not double-encrypt.
            return $str;
        }

        return base64_encode(Security::encrypt($str, $this->encKey()));
    }

    /**
     * Marshalling from form/request data: leave as plain text so beforeSave
     * can normalise (e.g. lowercase email) before the type encrypts on write.
     */
    public function marshal($value): ?string
    {
        if ($value === null || is_array($value)) {
            return null;
        }

        return (string)$value;
    }

    public function toStatement($value, DriverInterface $driver): int
    {
        return PDO::PARAM_STR;
    }

    /**
     * Must return true so CakePHP calls toPHP() when hydrating entities.
     */
    public function requiresToPhpCast(): bool
    {
        return true;
    }
}
