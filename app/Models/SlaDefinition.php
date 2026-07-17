<?php
declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\TimestampTrait;
use App\Models\Traits\SoftDeleteTrait;
use App\Models\Traits\UUIDTrait;
use App\Models\Traits\AuditTrait;

final class SlaDefinition extends BaseModel
{
    use TimestampTrait, SoftDeleteTrait, UUIDTrait, AuditTrait;

    private string $categoryId;
    private string $priority;
    private int $resolveHours;
    private int $escalateHours;

    public function __construct(array $data = [])
    {
        parent::__construct($data);
        $this->categoryId = $data['category_id'] ?? '';
        $this->priority = $data['priority'] ?? '';
        $this->resolveHours = (int)($data['resolve_hours'] ?? 72);
        $this->escalateHours = (int)($data['escalate_hours'] ?? 48);
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'category_id' => $this->categoryId,
            'priority' => $this->priority,
            'resolve_hours' => $this->resolveHours,
            'escalate_hours' => $this->escalateHours,
        ]);
    }
    
    public function getCategoryId(): string { return $this->categoryId; }
    public function getPriority(): string { return $this->priority; }
    public function getResolveHours(): int { return $this->resolveHours; }
    public function getEscalateHours(): int { return $this->escalateHours; }
}
