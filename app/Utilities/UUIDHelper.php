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
     */
    public static function toBinary(string $uuid): string
    {
        return hex2bin(str_replace('-', '', $uuid));
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
