<?php declare(strict_types=1);
namespace App\Models;
final class DistrictOfficer extends Officer
{
    public function getRoleName(): string      { return 'District Officer'; }
    public function getDashboardRoute(): string{ return '/dashboard'; }
}
