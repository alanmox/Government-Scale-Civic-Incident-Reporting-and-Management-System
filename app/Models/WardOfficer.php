<?php declare(strict_types=1);
namespace App\Models;
final class WardOfficer extends Officer
{
    public function getRoleName(): string      { return 'Ward Officer'; }
    public function getDashboardRoute(): string{ return '/dashboard'; }
}
