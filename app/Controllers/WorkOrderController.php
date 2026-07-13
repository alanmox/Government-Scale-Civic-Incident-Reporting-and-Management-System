<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\ValidationException;
use App\Repositories\IncidentRepository;
use App\Repositories\WorkOrderRepository;
use App\Services\WorkflowEngineService;
use App\Services\WorkOrderService;
use App\Utilities\UUIDHelper;

final class WorkOrderController extends BaseController
{
    private WorkOrderRepository $woRepo;
    private WorkOrderService $woService;

    public function __construct(
        \App\Core\Request $request, 
        \App\Core\Response $response
    ) {
        parent::__construct($request, $response);
        
        $this->woRepo = new WorkOrderRepository();
        $incidentRepo = new IncidentRepository();
        $workflow = new WorkflowEngineService($incidentRepo, new \App\Repositories\WorkflowLogRepository());
        
        $this->woService = new WorkOrderService($this->woRepo, $incidentRepo, $workflow);
    }

    /**
     * Officer Dashboard - List assigned work orders.
     */
    public function index(): void
    {
        $this->requireAuth();
        // Typically officers access this, need a specific permission like 'work_order.manage'
        // For now, assume any authenticated officer reaches here.

        $officerId = UUIDHelper::toBinary($this->session->userId());
        $workOrders = $this->woRepo->findByOfficer($officerId);

        $this->view('work_orders/index', [
            'pageTitle'  => 'My Work Orders',
            'workOrders' => $workOrders
        ]);
    }

    /**
     * Show Work Order details.
     */
    public function show(): void
    {
        $this->requireAuth();
        
        $woUuid = $this->request->routeParam('id');
        $binWoId = UUIDHelper::toBinary($woUuid);
        
        $woData = $this->woRepo->findById($binWoId);
        
        if (!$woData) {
            $this->redirectWithError('/work-orders', 'Work order not found.');
            return;
        }

        // Validate assignment
        if ($woData['officer_id'] !== UUIDHelper::toBinary($this->session->userId())) {
            $this->redirectWithError('/work-orders', __('error.403_message'));
            return;
        }

        $updates = $this->woRepo->getUpdates($binWoId);

        $this->view('work_orders/show', [
            'pageTitle' => $woData['reference_number'],
            'workOrder' => new \App\Models\WorkOrder($woData),
            'updates'   => $updates
        ]);
    }

    /**
     * Handle progress update submission.
     */
    public function updateProgress(): void
    {
        $this->requireAuth();
        
        $woUuid = $this->request->routeParam('id');
        $data = $this->request->all();

        try {
            if (empty($data['progress_percent']) || empty($data['notes'])) {
                throw new ValidationException(['general' => ['Progress and notes are required.']]);
            }

            $this->woService->updateProgress($woUuid, $this->session->userId(), $data);

            $this->redirectWithSuccess("/work-orders/{$woUuid}", 'Progress updated successfully.');

        } catch (ValidationException $e) {
            $this->redirectWithError("/work-orders/{$woUuid}", current($e->getErrors())[0]);
        } catch (\Throwable $e) {
            error_log("WorkOrder Update Error: " . $e->getMessage());
            $this->redirectWithError("/work-orders/{$woUuid}", __('error.500_message'));
        }
    }
}
