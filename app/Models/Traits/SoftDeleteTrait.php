<?php
declare(strict_types=1);
namespace App\Models\Traits;

/** Adds soft-delete awareness to models. */
trait SoftDeleteTrait
{
    public function isDeleted(): bool
    {
        return !empty($this->attributes['deleted_at']);
    }

    public function getDeletedAt(): ?string
    {
        return $this->attributes['deleted_at'] ?? null;
    }
}
