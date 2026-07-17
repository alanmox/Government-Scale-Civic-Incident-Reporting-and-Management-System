<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\ValidationException;
use App\Repositories\IncidentRepository;
use App\Utilities\UUIDHelper;

/**
 * Routing Engine Service
 * Responsible for directing incidents to the correct agency, department, or officer.
 */
final class RoutingEngineService extends BaseService
{
    private IncidentRepository $incidentRepo;

    public function __construct(IncidentRepository $incidentRepo)
    {
        parent::__construct();
        $this->incidentRepo = $incidentRepo;
    }

    /**
     * Assigns an incident to a specific officer.
     */
    public function assignToOfficer(string $incidentUuid, string $officerId): void
    {
        $binIncidentId = UUIDHelper::toBinary($incidentUuid);
        $incidentData = $this->incidentRepo->findById($binIncidentId);
        
        if (!$incidentData) {
            throw new ValidationException(['incident' => ['Incident not found.']]);
        }

        // Must be verified or already assigned to be reassigned
        if (!in_array($incidentData['status'], ['verified', 'assigned', 'in_progress'])) {
            throw new ValidationException(['status' => ['Incident must be verified before assignment.']]);
        }

        $this->incidentRepo->update($binIncidentId, [
            'assigned_officer_id' => UUIDHelper::toBinary($officerId),
            // Optionally update sub_status or trigger notifications here
        ]);
    }

    /**
     * Auto-route an incident based on its category and location.
     * Called during verification or submission.
     */
    public function autoRoute(string $incidentUuid): void
    {
        $binIncidentId = UUIDHelper::toBinary($incidentUuid);
        $incidentData = $this->incidentRepo->findById($binIncidentId);
        
        if (!$incidentData) {
            return;
        }
    }
}
