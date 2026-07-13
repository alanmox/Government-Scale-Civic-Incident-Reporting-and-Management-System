<?php
declare(strict_types=1);
namespace App\Models;

/**
 * Citizen — Primary reporter role.
 * OOP: Inheritance (User → Citizen), Polymorphism (overrides getRoleName/getDashboardRoute)
 */
final class Citizen extends User
{
    public function getRoleName(): string   { return 'Citizen'; }
    public function getDashboardRoute(): string { return '/dashboard'; }
    public function getRegionId(): ?string  { return $this->attributes['region_id']   ?? null; }
    public function getDistrictId(): ?string{ return $this->attributes['district_id'] ?? null; }
    public function getWardId(): ?string    { return $this->attributes['ward_id']     ?? null; }
    public function getVillageId(): ?string { return $this->attributes['village_id']  ?? null; }
    public function getPhysicalAddress(): ?string { return $this->attributes['physical_address'] ?? null; }
}
