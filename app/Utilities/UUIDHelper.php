<?php

declare(strict_types=1);

namespace App\Utilities;

use Ramsey\Uuid\Uuid;

/**
 * UUID Helper
 *
 * Wraps ramsey/uuid and provides binary conversion for MySQL BINARY(16) storage.
 */
final class UUIDHelper
{
    /**
     * Generates a new UUID v4 string.
     */
    public static function generate(): string
    {
        return Uuid::uuid4()->toString();
    }

    /**
     * Converts a UUID string to a 16-byte binary string for DB storage.
     *
     * Accepts:
     *  - Standard UUID string:  "ca9c8d79-8c7d-4a30-a1ba-9d2fcae61234"
     *  - Hex string (no dashes): "ca9c8d798c7d4a30a1ba9d2fcae61234"
     *
     * @throws \InvalidArgumentException If the value cannot be converted.
     */
    public static function toBinary(string $uuid): string
    {
        // Guard: if it's already 16-byte binary, return it directly.
        // This prevents double-conversion crashes.
        if (strlen($uuid) === 16) {
            return $uuid;
        }

        $hex = str_replace('-', '', $uuid);

        if (strlen($hex) !== 32 || !ctype_xdigit($hex)) {
            throw new \InvalidArgumentException(
                "Cannot convert to binary: value is not a valid UUID or hex string. Got: " . bin2hex(substr($uuid, 0, 8)) . "..."
            );
        }

        return hex2bin($hex);
    }

    /**
     * Converts a 16-byte binary string from the DB to a UUID string.
     */
    public static function toString(string $binary): string
    {
        $hex = bin2hex($binary);
        return sprintf(
            '%s-%s-%s-%s-%s',
            substr($hex, 0, 8),
            substr($hex, 8, 4),
            substr($hex, 12, 4),
            substr($hex, 16, 4),
            substr($hex, 20)
        );
    }

    /**
     * Validates whether a string is a valid UUID format.
     */
    public static function isValid(string $uuid): bool
    {
        return Uuid::isValid($uuid);
    }
}
