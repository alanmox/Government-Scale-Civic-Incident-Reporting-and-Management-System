<?php

declare(strict_types=1);

/**
 * Application Configuration
 *
 * All values sourced from .env via $_ENV.
 * Never access $_ENV directly in application code — use config('app.key').
 */

return [
    'name'      => $_ENV['APP_NAME']     ?? 'GCIRMS',
    'env'       => $_ENV['APP_ENV']      ?? 'production',
    'debug'     => filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOL),
    'url'       => $_ENV['APP_URL']      ?? 'http://localhost',
    'timezone'  => $_ENV['APP_TIMEZONE'] ?? 'Africa/Dar_es_Salaam',
    'locale'    => $_ENV['APP_LOCALE']   ?? 'en',
    'encryption_key' => $_ENV['APP_ENCRYPTION_KEY'] ?? (
        ($_ENV['APP_ENV'] ?? 'production') === 'production'
            ? throw new \RuntimeException('APP_ENCRYPTION_KEY must be set in production')
            : 'CHANGE_ME_IN_PRODUCTION'
    ),
    'version'   => '1.0.0',

    'pagination' => [
        'default' => (int) ($_ENV['PAGINATION_DEFAULT'] ?? 20),
        'max'     => (int) ($_ENV['PAGINATION_MAX']     ?? 100),
    ],

    'password' => [
        'min_length'     => (int) ($_ENV['PASSWORD_MIN_LENGTH']    ?? 8),
        'max_length'     => (int) ($_ENV['PASSWORD_MAX_LENGTH']    ?? 128),
        'history_count'  => (int) ($_ENV['PASSWORD_HISTORY_COUNT'] ?? 5),
        'expiry_days'    => (int) ($_ENV['PASSWORD_EXPIRY_DAYS']   ?? 90),
        'max_attempts'   => (int) ($_ENV['LOGIN_MAX_ATTEMPTS']     ?? 5),
        'lockout_minutes'=> (int) ($_ENV['LOGIN_LOCKOUT_MINUTES']  ?? 15),
    ],

    'sla' => [
        'default_response_hours'     => (int) ($_ENV['SLA_DEFAULT_RESPONSE']     ?? 24),
        'default_verification_hours' => (int) ($_ENV['SLA_DEFAULT_VERIFICATION'] ?? 48),
        'default_resolution_hours'   => (int) ($_ENV['SLA_DEFAULT_RESOLUTION']   ?? 168),
    ],

    'api' => [
        'rate_limit'        => (int) ($_ENV['API_RATE_LIMIT']        ?? 100),
        'rate_limit_window' => (int) ($_ENV['API_RATE_LIMIT_WINDOW'] ?? 60),
    ],
];
