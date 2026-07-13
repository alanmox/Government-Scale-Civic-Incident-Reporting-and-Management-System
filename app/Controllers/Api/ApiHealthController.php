<?php
declare(strict_types=1);
namespace App\Controllers\Api;

use App\Controllers\BaseController;

final class ApiHealthController extends BaseController
{
    public function index(): void
    {
        $this->apiSuccess([
            'status'  => 'ok',
            'version' => config('app.version', '1.0.0'),
            'time'    => now(),
        ], 'GCIRMS API is running.');
    }
}
