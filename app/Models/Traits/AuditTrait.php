<?php
declare(strict_types=1);
namespace App\Models\Traits;

/** Marks a model as auditable — the AuditService hooks into this. */
trait AuditTrait
{
    /** @var array<string, mixed> Snapshot before mutation (set by repository before update). */
    protected array $originalAttributes = [];

    public function setOriginal(array $attributes): void
    {
        $this->originalAttributes = $attributes;
    }

    /** @return array<string, mixed> */
    public function getOriginal(): array
    {
        return $this->originalAttributes;
    }

    /** Returns only the changed attributes (new values). */
    public function getDirty(): array
    {
        $dirty = [];
        foreach ($this->attributes as $key => $value) {
            if (!array_key_exists($key, $this->originalAttributes) ||
                $this->originalAttributes[$key] !== $value) {
                $dirty[$key] = $value;
            }
        }
        return $dirty;
    }
}
