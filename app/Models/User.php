<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\AuditTrait;
use App\Models\Traits\NotificationTrait;

/**
 * User — Concrete base model for all authenticated platform users.
 *
 * OOP Concepts: Inheritance (extends Person), Encapsulation
 * Maps to the `users` table.
 * Subclasses: Citizen, Officer, Administrator
 */
class User extends Person
{
    use AuditTrait;
    use NotificationTrait;

    /** @var string[] Columns that may be mass-assigned */
    protected function getFillable(): array
    {
        return [
            'id', 'uuid', 'agency_id', 'department_id',
            'full_name', 'username', 'email', 'phone', 'national_id',
            'profile_photo', 'gender', 'date_of_birth',
            'region_id', 'district_id', 'ward_id', 'village_id', 'physical_address',
            'status', 'email_verified_at', 'phone_verified_at',
            'two_factor_enabled', 'preferred_language', 'preferred_channel', 'timezone',
            'last_login_at', 'last_login_ip',
            'created_at', 'updated_at', 'created_by', 'updated_by', 'deleted_at',
        ];
    }

    // ── Identity ───────────────────────────────────────────────────────────────

    public function getId(): ?string
    {
        return $this->attributes['id'] ?? null;
    }

    public function getUuid(): string
    {
        return (string) ($this->attributes['uuid'] ?? '');
    }

    public function getUsername(): string
    {
        return (string) ($this->attributes['username'] ?? '');
    }

    public function getProfilePhoto(): ?string
    {
        return $this->attributes['profile_photo'] ?? null;
    }

    public function getProfilePhotoUrl(): string
    {
        return $this->getProfilePhoto()
            ? url('files/' . $this->getProfilePhoto())
            : '';
    }

    // ── Status ─────────────────────────────────────────────────────────────────

    public function getStatus(): string
    {
        return (string) ($this->attributes['status'] ?? 'inactive');
    }

    public function isActive(): bool
    {
        return $this->getStatus() === 'active';
    }

    public function isSuspended(): bool
    {
        return $this->getStatus() === 'suspended';
    }

    public function isLocked(): bool
    {
        return $this->getStatus() === 'locked';
    }

    public function isEmailVerified(): bool
    {
        return !empty($this->attributes['email_verified_at']);
    }

    // ── Organization ───────────────────────────────────────────────────────────

    public function getAgencyId(): ?string
    {
        return $this->attributes['agency_id'] ?? null;
    }

    public function getDepartmentId(): ?string
    {
        return $this->attributes['department_id'] ?? null;
    }

    // ── Security ───────────────────────────────────────────────────────────────

    public function getFailedLoginAttempts(): int
    {
        return (int) ($this->attributes['failed_login_attempts'] ?? 0);
    }

    public function isLockedOut(): bool
    {
        if (empty($this->attributes['locked_until'])) {
            return false;
        }
        return strtotime($this->attributes['locked_until']) > time();
    }

    // ── Preferences ────────────────────────────────────────────────────────────

    public function getPreferredLanguage(): string
    {
        return (string) ($this->attributes['preferred_language'] ?? 'en');
    }

    public function getTimezone(): string
    {
        return (string) ($this->attributes['timezone'] ?? 'Africa/Dar_es_Salaam');
    }

    // ── Person Abstract Implementation ─────────────────────────────────────────

    /**
     * OOP: Polymorphism — overridden by each subclass.
     */
    public function getRoleName(): string
    {
        return 'User';
    }

    public function getDashboardRoute(): string
    {
        return '/dashboard';
    }

    // ── Serialization (hide sensitive fields) ──────────────────────────────────

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = $this->attributes;
        // Never expose these in serialized output
        unset($data['password_hash'], $data['remember_token'],
              $data['two_factor_secret'], $data['email_verify_token']);
        return $data;
    }
}
