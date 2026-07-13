<?php

declare(strict_types=1);

/**
 * Cache Configuration
 */

return [
    'driver'      => $_ENV['CACHE_DRIVER']      ?? 'file',
    'path'        => BASE_PATH . '/' . ($_ENV['CACHE_PATH'] ?? 'storage/cache'),
    'default_ttl' => (int) ($_ENV['CACHE_DEFAULT_TTL'] ?? 3600),

    // Keys that are cached and their TTLs (seconds)
    'keys' => [
        'system_settings' => 86400,   // 24 hours
        'categories'      => 86400,
        'regions'         => 604800,  // 1 week (rarely changes)
        'roles'           => 3600,
        'permissions'     => 3600,
        'routing_rules'   => 3600,
        'escalation_rules'=> 3600,
    ],
];
