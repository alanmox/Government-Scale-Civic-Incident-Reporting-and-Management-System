<?php

declare(strict_types=1);

namespace App\Utilities;

/**
 * Password Hasher
 *
 * Wraps PHP's password_hash/password_verify with Argon2ID algorithm.
 * This is the ONLY class that should call these functions in the entire project.
 */
final class PasswordHasher
{
    private const ALGORITHM = PASSWORD_ARGON2ID;

    private const OPTIONS = [
        'memory_cost' => PASSWORD_ARGON2_DEFAULT_MEMORY_COST,
        'time_cost'   => PASSWORD_ARGON2_DEFAULT_TIME_COST,
        'threads'     => PASSWORD_ARGON2_DEFAULT_THREADS,
    ];

    /**
     * Hashes a password using Argon2ID, falling back to Bcrypt if unavailable.
     */
    public static function hash(string $plaintext): string
    {
        $algo = defined('PASSWORD_ARGON2ID') ? self::ALGORITHM : PASSWORD_BCRYPT;
        return password_hash($plaintext, $algo, self::OPTIONS);
    }

    /**
     * Verifies a plaintext password against a stored hash.
     */
    public static function verify(string $plaintext, string $hash): bool
    {
        return password_verify($plaintext, $hash);
    }

    /**
     * Checks if a hash needs to be rehashed (e.g., after algorithm upgrade).
     */
    public static function needsRehash(string $hash): bool
    {
        $algo = defined('PASSWORD_ARGON2ID') ? self::ALGORITHM : PASSWORD_BCRYPT;
        return password_needs_rehash($hash, $algo, self::OPTIONS);
    }
}
