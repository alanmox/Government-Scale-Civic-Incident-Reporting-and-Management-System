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

        // In a full implementation, this would look up complex rules in a `routing_rules` table.
        // For Phase 3, we rely on the `agency_id` that was set via the Category default during submission.
        
        // If we needed to push it down to a district level, we would look up the district's mapping here.
        // e.g. "If agency = Water Board AND district = Kinondoni, set department_id = Kinondoni Water Dept"
        
        // Example:
        // $departmentId = $this->findDepartmentForLocation($incidentData['agency_id'], $incidentData['district_id']);
        // $this->incidentRepo->update($binIncidentId, ['department_id' => $departmentId]);
    }
}
