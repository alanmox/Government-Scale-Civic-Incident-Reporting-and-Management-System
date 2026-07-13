<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\SessionManager;

/**
 * Base Service
 *
 * Provides all services with access to the session and common utilities.
 * Services contain business logic — they must not produce HTML or touch HTTP state.
 */
abstract class BaseService
{
    protected readonly SessionManager $session;

    public function __construct()
    {
        $this->session = SessionManager::getInstance();
    }

    /**
     * Returns the currently authenticated user's ID from session.
     */
    protected function currentUserId(): ?string
    {
        return $this->session->userId();
    }

    /**
     * Returns the current timestamp for created_at / updated_at fields.
     */
    protected function now(): string
    {
        return date('Y-m-d H:i:s');
    }
}
