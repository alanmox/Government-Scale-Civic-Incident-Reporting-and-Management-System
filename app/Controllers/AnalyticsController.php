<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\IncidentRepository;

/**
 * AnalyticsController — System-wide KPI analytics for supervisors and admins.
 */
final class AnalyticsController extends BaseController
{
    private IncidentRepository $incidentRepo;

    public function __construct(
        \App\Core\Request $request,
        \App\Core\Response $response
    ) {
        parent::__construct($request, $response);
        $this->incidentRepo = new IncidentRepository();
    }

    public function index(): void
    {
        $this->requireAuth();

        $this->view('analytics/index', [
            'pageTitle'   => 'Analytics & KPIs',
            'breadcrumbs' => [['label' => 'Analytics']],
            'byCategory'  => $this->incidentRepo->countByCategory(),
            'byStatus'    => $this->incidentRepo->countByStatus(),
            'trend'       => $this->incidentRepo->countByDay(),
            'agencyPerf'  => $this->incidentRepo->avgResolutionByAgency(),
            'slaBreaches' => $this->incidentRepo->countSlaBreaches(),
        ]);
    }
}
