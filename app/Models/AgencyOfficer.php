<?php declare(strict_types=1);
namespace App\Models;
final class AgencyOfficer extends Officer
{
    public function getRoleName(): string      { return 'Agency Officer'; }
    public function getDashboardRoute(): string{ return '/dashboard'; }
}
