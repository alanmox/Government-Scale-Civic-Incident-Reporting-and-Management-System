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
    /**
     * Returns the best available algorithm.
     */
    private static function algorithm(): string
    {
        return defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_BCRYPT;
    }

    /**
     * Returns algorithm options appropriate for the selected algorithm.
     */
    private static function options(): array
    {
        if (defined('PASSWORD_ARGON2ID')) {
            return [
                'memory_cost' => defined('PASSWORD_ARGON2_DEFAULT_MEMORY_COST') ? PASSWORD_ARGON2_DEFAULT_MEMORY_COST : 65536,
                'time_cost'   => defined('PASSWORD_ARGON2_DEFAULT_TIME_COST')   ? PASSWORD_ARGON2_DEFAULT_TIME_COST   : 4,
                'threads'     => defined('PASSWORD_ARGON2_DEFAULT_THREADS')     ? PASSWORD_ARGON2_DEFAULT_THREADS     : 1,
            ];
        }

        return [];
    }

    /**
     * Hashes a password using Argon2ID, falling back to Bcrypt if unavailable.
     */
    public static function hash(string $plaintext): string
    {
        return password_hash($plaintext, self::algorithm(), self::options());
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
        return password_needs_rehash($hash, self::algorithm(), self::options());
    }
}
