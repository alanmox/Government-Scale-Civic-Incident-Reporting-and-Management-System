<?php

declare(strict_types=1);

/**
 * Session Configuration
 */

return [
    'name'             => $_ENV['SESSION_NAME']             ?? 'gcirms_session',
    'lifetime'         => (int) ($_ENV['SESSION_LIFETIME']  ?? 1800),
    'absolute_lifetime'=> (int) ($_ENV['SESSION_ABSOLUTE_LIFETIME'] ?? 28800),
    'secure'           => filter_var($_ENV['SESSION_SECURE']   ?? false, FILTER_VALIDATE_BOOL),
    'httponly'         => filter_var($_ENV['SESSION_HTTPONLY']  ?? true,  FILTER_VALIDATE_BOOL),
    'samesite'         => $_ENV['SESSION_SAMESITE'] ?? 'Strict',
    'save_path'        => '',
];
