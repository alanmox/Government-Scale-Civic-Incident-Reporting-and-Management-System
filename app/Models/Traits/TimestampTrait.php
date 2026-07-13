<?php
declare(strict_types=1);
namespace App\Models\Traits;

/** Adds created_at / updated_at accessors to models. */
trait TimestampTrait
{
    public function getCreatedAt(): ?string { return $this->attributes['created_at'] ?? null; }
    public function getUpdatedAt(): ?string { return $this->attributes['updated_at'] ?? null; }

    /** Returns a human-readable relative time string. */
    public function createdAgo(): string
    {
        if (empty($this->attributes['created_at'])) { return ''; }
        $diff = time() - strtotime($this->attributes['created_at']);
        return match(true) {
            $diff < 60     => 'Just now',
            $diff < 3600   => floor($diff / 60) . ' minutes ago',
            $diff < 86400  => floor($diff / 3600) . ' hours ago',
            default        => floor($diff / 86400) . ' days ago',
        };
    }
}
