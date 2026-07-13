<?php declare(strict_types=1);
namespace App\Models;
final class SuperAdmin extends Administrator
{
    public function getRoleName(): string      { return 'Super Administrator'; }
    public function getDashboardRoute(): string{ return '/dashboard'; }
    /** Super admin always has all permissions — bypass DB check */
    public function hasAllPermissions(): bool  { return true; }
}
