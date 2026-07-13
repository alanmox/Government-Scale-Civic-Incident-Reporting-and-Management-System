<?php
declare(strict_types=1);
namespace App\Controllers;

/** Role-specific dashboard dispatcher — Phase 6 fills full implementation. */
final class DashboardController extends BaseController
{
    public function index(): void
    {
        $this->requireAuth();
        $roleId = $this->session->roleId();
        // Phase 6: dispatch to role-specific DashboardService method
        $this->view('dashboard/index', [
            'pageTitle' => __('nav.dashboard'),
            'roleId'    => $roleId,
        ]);
    }
}
