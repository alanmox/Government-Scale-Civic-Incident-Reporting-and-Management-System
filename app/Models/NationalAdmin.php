<?php declare(strict_types=1);
namespace App\Models;
final class NationalAdmin extends Administrator
{
    public function getRoleName(): string      { return 'National Administrator'; }
    public function getDashboardRoute(): string{ return '/dashboard'; }
}
