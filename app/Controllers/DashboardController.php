<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Utilities\UUIDHelper;

/** Role-specific dashboard dispatcher — Phase 6 fills full implementation. */
final class DashboardController extends BaseController
{
    public function index(): void
    {
        $this->requireAuth();
        
        $userBinId = $this->session->userId();
        $roleSlug = $this->session->get('user_role');
        
        $incidentRepo = new \App\Repositories\IncidentRepository();
        $woRepo = new \App\Repositories\WorkOrderRepository();
        $dashService = new \App\Services\DashboardService($incidentRepo, $woRepo);

        // Simplified Role Routing
        if ($roleSlug === 'citizen') {
            $userId = UUIDHelper::toString($userBinId);
            $stats = $dashService->getCitizenStats($userId);
            $view = 'dashboard/citizen';
        } elseif (in_array($roleSlug, ['agency_officer', 'ward_officer'])) {
            $userId = UUIDHelper::toString($userBinId);
            $stats = $dashService->getOfficerStats($userId);
            $view = 'dashboard/officer';
        } else {
            // Default to admin/system overview for everyone else
            $stats = $dashService->getSystemStats();
            $view = 'dashboard/admin';
        }

        $this->view($view, [
            'pageTitle' => __('nav.dashboard'),
            'stats'     => $stats,
            'roleSlug'  => $roleSlug
        ]);
    }
}
