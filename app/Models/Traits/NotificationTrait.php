<?php
declare(strict_types=1);
namespace App\Models\Traits;

/** Marks a model's owner as notifiable. */
trait NotificationTrait
{
    public function getEmail(): string { return (string) ($this->attributes['email'] ?? ''); }
    public function getPhone(): ?string { return $this->attributes['phone'] ?? null; }
    public function prefersChannel(): string { return $this->attributes['preferred_channel'] ?? 'in_app'; }
}
