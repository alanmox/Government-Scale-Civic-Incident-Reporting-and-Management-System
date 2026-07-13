<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\ValidationException;
use App\Models\Incident;
use App\Repositories\CategoryRepository;
use App\Repositories\IncidentRepository;
use App\Utilities\UUIDHelper;

final class IncidentService extends BaseService
{
    private IncidentRepository $incidentRepo;
    private CategoryRepository $categoryRepo;

    public function __construct(IncidentRepository $incidentRepo, CategoryRepository $categoryRepo)
    {
        parent::__construct();
        $this->incidentRepo = $incidentRepo;
        $this->categoryRepo = $categoryRepo;
    }

    /**
     * Report a new incident.
     */
    public function reportIncident(array $data, string $citizenId): Incident
    {
        // 1. Basic Validation (In a real app, use a dedicated Validator)
        if (empty($data['title']) || empty($data['description']) || empty($data['category_id'])) {
            throw new ValidationException(['general' => ['Missing required fields.']]);
        }

        // 2. Look up category to determine default priority, SLA, and agency
        $categoryData = $this->categoryRepo->findById($data['category_id']);
        if (!$categoryData) {
            throw new ValidationException(['category_id' => ['Invalid category selected.']]);
        }

        // 3. Prepare data
        $incidentId = UUIDHelper::generate();
        $binIncidentId = UUIDHelper::toBinary($incidentId);
        $refNumber = $this->incidentRepo->generateReferenceNumber();

        $priority = $categoryData['default_priority'];
        // Allow user to override priority ONLY if it's lower or same, but for simplicity, we'll use category default
        // In a real system, citizens might not be trusted to set priority accurately.

        // Calculate SLA Due At
        $slaHours = (int) $categoryData['sla_hours'];
        $slaDueAt = date('Y-m-d H:i:s', time() + ($slaHours * 3600));

        $incidentData = [
            'id'               => $binIncidentId,
            'reference_number' => $refNumber,
            'citizen_id'       => UUIDHelper::toBinary($citizenId),
            'category_id'      => $categoryData['id'], // already binary
            'title'            => trim($data['title']),
            'description'      => trim($data['description']),
            'priority'         => $priority,
            'status'           => 'submitted',
            'region_id'        => !empty($data['region_id']) ? UUIDHelper::toBinary($data['region_id']) : null,
            'district_id'      => !empty($data['district_id']) ? UUIDHelper::toBinary($data['district_id']) : null,
            'ward_id'          => !empty($data['ward_id']) ? UUIDHelper::toBinary($data['ward_id']) : null,
            'village_id'       => !empty($data['village_id']) ? UUIDHelper::toBinary($data['village_id']) : null,
            'latitude'         => $data['latitude'] ?? null,
            'longitude'        => $data['longitude'] ?? null,
            'location_desc'    => trim($data['location_desc'] ?? ''),
            'sla_due_at'       => $slaDueAt,
            'agency_id'        => $categoryData['agency_id'], // Default routing based on category
            'is_public'        => !empty($data['is_public']) ? 1 : 0,
        ];

        // 4. Save
        $this->incidentRepo->create($incidentData);

        // Fetch saved instance to return
        $saved = $this->incidentRepo->findById($binIncidentId);
        return new Incident($saved);
    }
}
