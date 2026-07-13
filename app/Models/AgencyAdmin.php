<?php declare(strict_types=1);
namespace App\Models;
final class AgencyAdmin extends Administrator
{
    public function getRoleName(): string      { return 'Agency Administrator'; }
    public function getDashboardRoute(): string{ return '/dashboard'; }
}
