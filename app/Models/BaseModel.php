<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Base Model
 *
 * Lightweight data container. Models hold entity state, getters/setters,
 * and lightweight logic only. No SQL, no business logic, no HTML.
 *
 * Subclasses declare their properties using the OOP hierarchy:
 *   Person → User → Citizen / Officer / Administrator
 *   BaseIncident → WaterIncident / RoadIncident / ...
 */
abstract class BaseModel
{
    /** @var array<string, mixed> Raw data backing store */
    protected array $attributes = [];

    /**
     * @param array<string, mixed> $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->fill($attributes);
    }

    /**
     * Mass-assigns attributes (whitelist enforced by subclasses via $fillable).
     *
     * @param array<string, mixed> $data
     */
    public function fill(array $data): static
    {
        $fillable = $this->getFillable();

        foreach ($data as $key => $value) {
            if (empty($fillable) || in_array($key, $fillable, true)) {
                $this->attributes[$key] = $value;
            }
        }

        return $this;
    }

    /**
     * Returns attributes that may be mass-assigned.
     * Subclasses override to restrict fillable fields.
     *
     * @return string[]
     */
    protected function getFillable(): array
    {
        return [];
    }

    // ── Magic Accessors ────────────────────────────────────────────────────────

    public function __get(string $name): mixed
    {
        return $this->attributes[$name] ?? null;
    }

    public function __set(string $name, mixed $value): void
    {
        $this->attributes[$name] = $value;
    }

    public function __isset(string $name): bool
    {
        return isset($this->attributes[$name]);
    }

    // ── Serialization ──────────────────────────────────────────────────────────

    /**
     * Returns all attributes as an array (for passing to views or APIs).
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->attributes;
    }

    /**
     * Returns JSON representation of the model.
     */
    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_UNESCAPED_UNICODE) ?: '{}';
    }

    // ── Audit Columns ──────────────────────────────────────────────────────────

    public function getCreatedAt(): ?string
    {
        return $this->attributes['created_at'] ?? null;
    }

    public function getUpdatedAt(): ?string
    {
        return $this->attributes['updated_at'] ?? null;
    }

    public function getDeletedAt(): ?string
    {
        return $this->attributes['deleted_at'] ?? null;
    }

    public function isDeleted(): bool
    {
        return !empty($this->attributes['deleted_at']);
    }
}
