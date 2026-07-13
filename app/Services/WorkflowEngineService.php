<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\ValidationException;
use App\Models\Incident;
use App\Repositories\IncidentRepository;
use App\Repositories\WorkflowLogRepository;
use App\Utilities\UUIDHelper;

/**
 * Workflow Engine Service
 * Manages the state machine transitions for an incident.
 */
final class WorkflowEngineService extends BaseService
{
    private IncidentRepository $incidentRepo;
    private WorkflowLogRepository $workflowLogRepo;

    // Defines allowed transitions: 'current_state' => ['allowed_next_state_1', 'allowed_next_state_2']
    private array $allowedTransitions = [
        'draft'        => ['submitted'],
        'submitted'    => ['verified', 'rejected'],
        'verified'     => ['assigned', 'rejected'],
        'assigned'     => ['in_progress', 'rejected'],
        'in_progress'  => ['resolved', 'assigned'], // can reassign
        'resolved'     => ['closed', 'in_progress'], // citizen can reject resolution
        'closed'       => ['archived'],
        'rejected'     => ['archived', 'submitted'], // can be appealed
        'archived'     => []
    ];

    public function __construct(IncidentRepository $incidentRepo, WorkflowLogRepository $workflowLogRepo)
    {
        parent::__construct();
        $this->incidentRepo = $incidentRepo;
        $this->workflowLogRepo = $workflowLogRepo;
    }

    /**
     * Attempts to transition an incident to a new status.
     *
     * @param string $incidentUuid The human readable UUID of the incident.
     * @param string $toStatus     The target status.
     * @param string $actorId      The ID of the user performing the action.
     * @param string $actionName   The name of the action (e.g. 'verify', 'reject').
     * @param string|null $comments Optional reason.
     * @throws ValidationException If the transition is not allowed.
     */
    public function transition(
        string $incidentUuid, 
        string $toStatus, 
        string $actorId, 
        string $actionName, 
        ?string $comments = null
    ): void {
        $binIncidentId = UUIDHelper::toBinary($incidentUuid);
        $incidentData = $this->incidentRepo->findById($binIncidentId);
        
        if (!$incidentData) {
            throw new ValidationException(['incident' => ['Incident not found.']]);
        }

        $fromStatus = $incidentData['status'];

        if (!$this->canTransition($fromStatus, $toStatus)) {
            throw new ValidationException([
                'status' => ["Cannot transition incident from '{$fromStatus}' to '{$toStatus}'."]
            ]);
        }

        // Use transaction to ensure consistency
        $this->incidentRepo->transaction(function() use ($binIncidentId, $fromStatus, $toStatus, $actorId, $actionName, $comments) {
            // Update the status on the incident
            $updateData = ['status' => $toStatus];
            
            // Special handling for resolution
            if ($toStatus === 'resolved') {
                $updateData['resolved_at'] = date('Y-m-d H:i:s');
            } elseif ($fromStatus === 'resolved' && $toStatus === 'in_progress') {
                $updateData['resolved_at'] = null; // Re-opened
            }

            $this->incidentRepo->update($binIncidentId, $updateData);

            // Record in audit log
            $this->workflowLogRepo->logTransition(
                $binIncidentId,
                UUIDHelper::toBinary($actorId),
                $actionName,
                $fromStatus,
                $toStatus,
                $comments
            );
        });
    }

    /**
     * Checks if a transition is valid according to the state machine rules.
     */
    public function canTransition(string $fromStatus, string $toStatus): bool
    {
        if (!isset($this->allowedTransitions[$fromStatus])) {
            return false;
        }
        
        return in_array($toStatus, $this->allowedTransitions[$fromStatus], true);
    }
}
