<?php

declare(strict_types=1);

/**
 * Upload Configuration
 */

return [
    'max_size'      => (int) ($_ENV['UPLOAD_MAX_SIZE'] ?? 10485760), // 10 MB
    'allowed_types' => explode(',', $_ENV['UPLOAD_ALLOWED_TYPES'] ?? 'jpg,jpeg,png,gif,pdf,doc,docx,mp4'),
    'blocked_types' => ['php', 'php3', 'php4', 'php5', 'phtml', 'exe', 'bat', 'sh', 'js', 'html', 'htm'],
    'path'          => BASE_PATH . '/' . ($_ENV['UPLOAD_PATH'] ?? 'storage/uploads'),

    'allowed_mimes' => [
        'image/jpeg', 'image/png', 'image/gif',
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'video/mp4',
    ],

    'image_max_width'  => 2048,
    'image_max_height' => 2048,
    'thumbnail_width'  => 300,
    'thumbnail_height' => 300,

    // Contexts for organized storage
    'contexts' => [
        'incident'   => 'incidents',
        'profile'    => 'profiles',
        'evidence'   => 'evidence',
        'workorder'  => 'workorders',
        'report'     => 'reports',
        'comment'    => 'comments',
    ],
];
