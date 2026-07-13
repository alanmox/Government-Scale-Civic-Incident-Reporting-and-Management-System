<?php declare(strict_types=1);
namespace App\Models;
final class RegionalOfficer extends Officer
{
    public function getRoleName(): string      { return 'Regional Officer'; }
    public function getDashboardRoute(): string{ return '/dashboard'; }
}
