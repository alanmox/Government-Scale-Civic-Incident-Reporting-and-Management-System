<?php

declare(strict_types=1);

namespace App\Models\Traits;

use App\Utilities\UUIDHelper;

/**
 * UUID Trait
 *
 * Provides UUID generation and binary conversion helpers.
 * Applied to models and repositories that use BINARY(16) UUID primary keys.
 */
trait UUIDTrait
{
    /**
     * Generates a new UUID v4 string.
     */
    public static function generateUuid(): string
    {
        return UUIDHelper::generate();
    }

    /**
     * Converts a UUID string to binary(16) for DB storage.
     */
    public static function uuidToBin(string $uuid): string
    {
        return UUIDHelper::toBinary($uuid);
    }

    /**
     * Converts binary(16) from DB back to UUID string.
     */
    public static function binToUuid(string $binary): string
    {
        return UUIDHelper::toString($binary);
    }
}
