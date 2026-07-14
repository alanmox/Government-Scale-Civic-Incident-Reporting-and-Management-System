<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\ValidationException;
use App\Repositories\IncidentRepository;
use App\Repositories\WorkOrderRepository;
use App\Utilities\UUIDHelper;

final class WorkOrderService extends BaseService
{
    private WorkOrderRepository $woRepo;
    private IncidentRepository $incidentRepo;
    private WorkflowEngineService $workflow;

    public function __construct(
        WorkOrderRepository $woRepo, 
        IncidentRepository $incidentRepo,
        WorkflowEngineService $workflow
    ) {
        parent::__construct();
        $this->woRepo = $woRepo;
        $this->incidentRepo = $incidentRepo;
        $this->workflow = $workflow;
    }

    /**
     * Creates a new work order from an incident assignment.
     */
    public function createFromIncident(string $incidentUuid, string $officerUuid, array $data): void
    {
        $binIncidentId = UUIDHelper::toBinary($incidentUuid);
        $binOfficerId  = UUIDHelper::toBinary($officerUuid);
        
        $incidentData = $this->incidentRepo->findById($binIncidentId);
        if (!$incidentData) {
            throw new ValidationException(['incident' => ['Incident not found.']]);
        }

        $refNumber = $this->woRepo->generateReferenceNumber();
        $id = UUIDHelper::toBinary(UUIDHelper::generate());

        $this->woRepo->create([
            'id'               => $id,
            'incident_id'      => $binIncidentId,
            'officer_id'       => $binOfficerId,
            'reference_number' => $refNumber,
            'title'            => 'WO for: ' . $incidentData['title'],
            'description'      => $data['description'] ?? 'Work order created for incident resolution.',
            'priority'         => $incidentData['priority'],
            'status'           => 'pending'
        ]);
    }

    /**
     * Log progress update and possibly resolve.
     */
    public function updateProgress(string $woUuid, string $officerId, array $data): void
    {
        $binWoId = UUIDHelper::toBinary($woUuid);
        $woData = $this->woRepo->findById($binWoId);
        
        if (!$woData) {
            throw new ValidationException(['work_order' => ['Work order not found.']]);
        }

        // Validate Officer Authorization
        if ($woData['officer_id'] !== UUIDHelper::toBinary($officerId)) {
            throw new ValidationException(['auth' => ['You are not assigned to this work order.']]);
        }

        // 1. Add Update
        $this->woRepo->addUpdate([
            'id'               => UUIDHelper::toBinary(UUIDHelper::generate()),
            'work_order_id'    => $binWoId,
            'officer_id'       => UUIDHelper::toBinary($officerId),
            'progress_percent' => (int) $data['progress_percent'],
            'notes'            => trim($data['notes']),
            'is_internal'      => !empty($data['is_internal']) ? 1 : 0
        ]);

        // 2. Update Work Order Status
        $woUpdates = [];
        $progress = (int) $data['progress_percent'];
        
        if ($progress > 0 && $woData['status'] === 'pending') {
            $woUpdates['status'] = 'in_progress';
            $woUpdates['started_at'] = date('Y-m-d H:i:s');
        } elseif ($progress === 100) {
            $woUpdates['status'] = 'completed';
            $woUpdates['completed_at'] = date('Y-m-d H:i:s');
            if (!empty($data['actual_cost'])) {
                $woUpdates['actual_cost'] = $data['actual_cost'];
            }
        }
        
        if (!empty($woUpdates)) {
            $this->woRepo->update($binWoId, $woUpdates);
        }

        // 3. Sync back to Incident Status if completed
        if ($progress === 100) {
            $incidentUuid = UUIDHelper::toString($woData['incident_id']);
            $this->workflow->transition(
                $incidentUuid, 
                'resolved', 
                $officerId, 
                'resolve_incident', 
                'Work order completed: ' . $woData['reference_number']
            );
        }
    }
}
