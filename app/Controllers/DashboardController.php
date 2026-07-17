<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Repositories\IncidentRepository;
use App\Repositories\WorkOrderRepository;
use App\Services\DashboardService;
use App\Utilities\UUIDHelper;

final class DashboardController extends BaseController
{
    private DashboardService $dashService;

    public function __construct(
        Request $request,
        Response $response
    ) {
        parent::__construct($request, $response);
        $incidentRepo = new IncidentRepository();
        $woRepo = new WorkOrderRepository();
        $this->dashService = new DashboardService($incidentRepo, $woRepo);
    }

    public function index(): void
    {
        $this->requireAuth();

        $userBinId = $this->session->userId();
        $roleSlug = $this->session->get('user_role');

        if ($roleSlug === 'citizen') {
            $userId = UUIDHelper::toString($userBinId);
            $stats = $this->dashService->getCitizenStats($userId);
            $view = 'dashboard/citizen';
        } else {
            $stats = $this->dashService->getSystemStats();
            $view = 'dashboard/admin';
        }

        $this->view($view, [
            'pageTitle' => __('nav.dashboard'),
            'stats'     => $stats,
            'roleSlug'  => $roleSlug
        ]);
    }
}
