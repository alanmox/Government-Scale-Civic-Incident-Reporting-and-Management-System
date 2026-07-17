<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Request;
use App\Core\Response;
use App\Repositories\IncidentRepository;
use App\Utilities\UUIDHelper;

final class ApiIncidentController extends ApiController
{
    private IncidentRepository $incidentRepo;

    public function __construct(Request $request, Response $response)
    {
        parent::__construct($request, $response);
        $this->incidentRepo = new IncidentRepository();
    }

    /**
     * GET /api/v1/incidents
     * Retrieve a paginated list of public/recent incidents.
     */
    public function index(): void
    {
        $page = (int) ($this->request->query('page') ?? 1);
        $limit = (int) ($this->request->query('limit') ?? 20);
        $offset = ($page - 1) * $limit;

        // In a real API, enforce max limits (e.g., $limit <= 100)
        
        $criteria = ['status' => 'verified']; // Example: Only show verified public data to API consumers
        
        $incidents = $this->incidentRepo->search($criteria, $limit, $offset);
        $total = $this->incidentRepo->searchCount($criteria);

        $this->success($incidents, 'Incidents retrieved successfully', [
            'pagination' => [
                'current_page' => $page,
                'per_page'     => $limit,
                'total_items'  => $total,
                'total_pages'  => ceil($total / $limit)
            ]
        ]);
    }

    /**
     * GET /api/v1/incidents/{id}
     * Retrieve a specific incident.
     */
    public function show(): void
    {
        $uuid = $this->request->routeParam('id');
        
        try {
            $binId = UUIDHelper::toBinary($uuid);
            $incident = $this->incidentRepo->findById($binId);
            
            if (!$incident) {
                $this->error('Incident not found.', null, 404);
                return;
            }

            // Exclude sensitive data if needed
            unset($incident['id']);
            unset($incident['citizen_id']);
            unset($incident['assigned_officer_id']);

            $this->success($incident, 'Incident retrieved successfully');

        } catch (\InvalidArgumentException $e) {
            $this->error('Invalid UUID format.', null, 400);
        } catch (\Throwable $e) {
            $this->error('Internal Server Error.', null, 500);
        }
    }
}
