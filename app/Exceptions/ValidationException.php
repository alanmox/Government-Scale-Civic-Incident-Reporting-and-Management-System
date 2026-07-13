<?php
declare(strict_types=1);
namespace App\Exceptions;

/**
 * Thrown when form/input validation fails.
 * Carries field-level error messages.
 */
class ValidationException extends AppException
{
    /** @var array<string, string[]> */
    private array $errors;

    /** @param array<string, string[]> $errors */
    public function __construct(array $errors, string $message = 'Validation failed.')
    {
        parent::__construct($message, 422);
        $this->errors = $errors;
    }

    /** @return array<string, string[]> */
    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getFirstError(): ?string
    {
        foreach ($this->errors as $fieldErrors) {
            return $fieldErrors[0] ?? null;
        }
        return null;
    }
}
