<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Person — Abstract Root of User Hierarchy
 *
 * OOP Concept: Abstract class / Abstraction
 * Defines the common contract for all human actors in the system.
 * Cannot be instantiated directly.
 *
 * Hierarchy: Person → User → Citizen / Officer / Administrator
 */
abstract class Person extends BaseModel
{
    use Traits\TimestampTrait;
    use Traits\SoftDeleteTrait;
    use Traits\UUIDTrait;

    /**
     * Returns the person's full name.
     * Demonstrates encapsulation — direct attribute access is via getter.
     */
    public function getFullName(): string
    {
        return (string) ($this->attributes['full_name'] ?? '');
    }

    /**
     * Returns the person's email address.
     */
    public function getEmail(): string
    {
        return (string) ($this->attributes['email'] ?? '');
    }

    /**
     * Returns the person's phone number (may be decrypted).
     */
    public function getPhone(): ?string
    {
        return $this->attributes['phone'] ?? null;
    }

    /**
     * Returns a display-safe name (first name + last initial).
     */
    public function getDisplayName(): string
    {
        $parts = explode(' ', $this->getFullName(), 2);
        if (count($parts) === 2) {
            return $parts[0] . ' ' . strtoupper($parts[1][0] ?? '') . '.';
        }
        return $parts[0];
    }

    /**
     * Returns the person's initials for avatar fallback.
     */
    public function getInitials(): string
    {
        $words = array_filter(explode(' ', $this->getFullName()));
        $initials = '';
        foreach (array_slice($words, 0, 2) as $word) {
            $initials .= strtoupper($word[0]);
        }
        return $initials ?: 'U';
    }

    /**
     * Abstract: each subclass defines its own role identifier.
     */
    abstract public function getRoleName(): string;

    /**
     * Abstract: each subclass defines its dashboard route.
     */
    abstract public function getDashboardRoute(): string;
}
