<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Category Model
 * Maps to the incident_categories table.
 */
class Category extends BaseModel
{
    use Traits\TimestampTrait;
    use Traits\SoftDeleteTrait;
    use Traits\AuditTrait;

    protected function getFillable(): array
    {
        return [
            'id', 'name', 'slug', 'description', 'default_priority',
            'sla_hours', 'agency_id', 'is_active',
            'created_at', 'updated_at', 'created_by', 'updated_by', 'deleted_at', 'deleted_by'
        ];
    }

    public function getId(): string
    {
        return (string) ($this->attributes['id'] ?? '');
    }

    public function getName(): string
    {
        return (string) ($this->attributes['name'] ?? '');
    }

    public function getSlug(): string
    {
        return (string) ($this->attributes['slug'] ?? '');
    }

    public function getDefaultPriority(): string
    {
        return (string) ($this->attributes['default_priority'] ?? 'medium');
    }

    public function getSlaHours(): int
    {
        return (int) ($this->attributes['sla_hours'] ?? 72);
    }

    public function getAgencyId(): ?string
    {
        return $this->attributes['agency_id'] ?? null;
    }

    public function isActive(): bool
    {
        return !empty($this->attributes['is_active']);
    }
}
