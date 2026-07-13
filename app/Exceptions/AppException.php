<?php

declare(strict_types=1);

namespace App\Exceptions;

/**
 * Base Application Exception
 *
 * All GCIRMS custom exceptions extend this class.
 * Allows catch-all handling for application-specific errors.
 */
class AppException extends \RuntimeException
{
    /** @var array<string, mixed> */
    protected array $context = [];

    /**
     * @param array<string, mixed> $context Additional debug context (never expose to users)
     */
    public function __construct(
        string $message = '',
        int $code = 0,
        ?\Throwable $previous = null,
        array $context = []
    ) {
        parent::__construct($message, $code, $previous);
        $this->context = $context;
    }

    /**
     * @return array<string, mixed>
     */
    public function getContext(): array
    {
        return $this->context;
    }
}
