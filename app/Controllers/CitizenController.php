<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\IncidentRepository;
use App\Repositories\WorkOrderRepository;
use App\Utilities\UUIDHelper;

final class CitizenController extends BaseController
{
    private IncidentRepository $incidentRepo;
    private WorkOrderRepository $workOrderRepo;

    public function __construct(
        \App\Core\Request $request,
        \App\Core\Response $response
    ) {
        parent::__construct($request, $response);
        $this->incidentRepo = new IncidentRepository();
        $this->workOrderRepo = new WorkOrderRepository();
    }

    /** My Impact — gamification stats for the citizen. */
    public function impact(): void
    {
        $this->requireAuth();

        $binId = UUIDHelper::toBinary($this->session->userId());
        $data = $this->incidentRepo->getCitizenStats($binId);

        $this->view('citizen/impact', [
            'pageTitle'   => 'My Impact',
            'breadcrumbs' => [['label' => 'My Impact']],
            'stats'       => $data
        ]);
    }

    /** Notification preferences self-service page. */
    public function notificationSettings(): void
    {
        $this->requireAuth();
        $this->view('citizen/notification_settings', [
            'pageTitle'   => 'Alert Settings',
            'breadcrumbs' => [['label' => 'Alert Settings']]
        ]);
    }

    /** Bookmarked community reports. */
    public function bookmarks(): void
    {
        $this->requireAuth();
        $this->view('citizen/bookmarks', [
            'pageTitle'   => 'Bookmarks',
            'breadcrumbs' => [['label' => 'Bookmarks']],
            'bookmarks'   => []
        ]);
    }

    /** Updates inbox — officer messages on citizen's reports. */
    public function updates(): void
    {
        $this->requireAuth();
        $binId = UUIDHelper::toBinary($this->session->userId());
        $updates = $this->workOrderRepo->getUpdatesForCitizen($binId);

        $this->view('citizen/updates', [
            'pageTitle'   => 'Updates Inbox',
            'breadcrumbs' => [['label' => 'Updates Inbox']],
            'updates'     => $updates
        ]);
    }
}
