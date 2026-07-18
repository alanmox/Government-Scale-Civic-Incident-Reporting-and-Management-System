<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Incident Model
 * Maps to the incidents table.
 */
class Incident extends BaseModel
{
    use Traits\TimestampTrait;
    use Traits\SoftDeleteTrait;
    use Traits\UUIDTrait;

    protected function getFillable(): array
    {
        return [
            'id', 'uuid_str', 'reference_number', 'citizen_id', 'category_id',
            'category_name', 'citizen_name',
            'title', 'description', 'priority', 'status', 'sub_status',
            'region_id', 'district_id', 'ward_id', 'village_id',
            'latitude', 'longitude', 'location_desc',
            'reported_at', 'sla_due_at', 'resolved_at',
            'agency_id', 'department_id', 'assigned_officer_id',
            'assigned_officer_name',
            'is_public', 'created_at', 'updated_at', 'deleted_at', 'deleted_by'
        ];
    }

    public function getId(): string
    {
        return (string) ($this->attributes['id'] ?? '');
    }

    public function getUuid(): string
    {
        return (string) ($this->attributes['uuid_str'] ?? '');
    }

    public function getReferenceNumber(): string
    {
        return (string) ($this->attributes['reference_number'] ?? '');
    }

    public function getTitle(): string
    {
        return (string) ($this->attributes['title'] ?? '');
    }

    public function getDescription(): string
    {
        return (string) ($this->attributes['description'] ?? '');
    }

    public function getStatus(): string
    {
        return (string) ($this->attributes['status'] ?? 'draft');
    }

    public function getPriority(): string
    {
        return (string) ($this->attributes['priority'] ?? 'medium');
    }

    public function getCategoryName(): ?string
    {
        return $this->attributes['category_name'] ?? null;
    }

    public function getCitizenName(): ?string
    {
        return $this->attributes['citizen_name'] ?? null;
    }

    public function getAssignedOfficerName(): ?string
    {
        return $this->attributes['assigned_officer_name'] ?? null;
    }

    /**
     * Helper to get CSS class for status.
     */
    public function getStatusBadgeClass(): string
    {
        return 'badge-status badge-' . $this->getStatus();
    }
    
    /**
     * Helper to get CSS class for priority.
     */
    public function getPriorityBadgeClass(): string
    {
        return 'badge-status badge-' . $this->getPriority();
    }

    /**
     * Calculate if SLA is breached based on current time.
     */
    public function isSlaBreached(): bool
    {
        if (empty($this->attributes['sla_due_at']) || in_array($this->getStatus(), ['resolved', 'closed', 'archived'])) {
            return false;
        }
        return strtotime($this->attributes['sla_due_at']) < time();
    }
}
