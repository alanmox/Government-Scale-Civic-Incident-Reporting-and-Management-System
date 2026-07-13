<?php
declare(strict_types=1);
namespace App\Models;

/**
 * Officer — Abstract base for all government officer subtypes.
 * OOP: Inheritance (User → Officer), Abstraction (forces subclasses to define role)
 */
abstract class Officer extends User
{
    public function getAgencyName(): ?string     { return $this->attributes['agency_name']     ?? null; }
    public function getDepartmentName(): ?string { return $this->attributes['department_name'] ?? null; }
}
