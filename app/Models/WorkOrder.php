<?php

declare(strict_types=1);

namespace App\Models;

/**
 * WorkOrder Model
 * Maps to the work_orders table.
 */
class WorkOrder extends BaseModel
{
    use Traits\TimestampTrait;
    use Traits\SoftDeleteTrait;
    use Traits\UUIDTrait;

    protected function getFillable(): array
    {
        return [
            'id', 'incident_id', 'officer_id', 'reference_number',
            'title', 'description', 'status', 'priority',
            'estimated_cost', 'actual_cost',
            'started_at', 'completed_at',
            'created_at', 'updated_at', 'deleted_at'
        ];
    }

    public function getReferenceNumber(): string
    {
        return (string) ($this->attributes['reference_number'] ?? '');
    }

    public function getTitle(): string
    {
        return (string) ($this->attributes['title'] ?? '');
    }

    public function getStatus(): string
    {
        return (string) ($this->attributes['status'] ?? 'pending');
    }

    public function getStatusBadgeClass(): string
    {
        $status = $this->getStatus();
        return match ($status) {
            'pending'     => 'badge bg-secondary',
            'in_progress' => 'badge bg-warning text-dark',
            'on_hold'     => 'badge bg-danger',
            'completed'   => 'badge bg-success',
            'cancelled'   => 'badge bg-dark',
            default       => 'badge bg-light text-dark'
        };
    }
}
