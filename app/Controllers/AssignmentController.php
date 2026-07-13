<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\ValidationException;
use App\Repositories\IncidentRepository;
use App\Repositories\UserRepository;
use App\Services\RoutingEngineService;
use App\Services\WorkflowEngineService;

final class AssignmentController extends BaseController
{
    private IncidentRepository $incidentRepo;
    private UserRepository $userRepo;
    private RoutingEngineService $routingService;
    private WorkflowEngineService $workflow;

    public function __construct(
        \App\Core\Request $request, 
        \App\Core\Response $response
    ) {
        parent::__construct($request, $response);
        
        $this->incidentRepo = new IncidentRepository();
        $this->userRepo = new UserRepository();
        $this->routingService = new RoutingEngineService($this->incidentRepo);
        $this->workflow = new WorkflowEngineService(
            $this->incidentRepo, 
            new \App\Repositories\WorkflowLogRepository()
        );
    }

    /**
     * Display incidents ready for assignment and available officers.
     */
    public function index(): void
    {
        $this->requireAuth();
        $this->requirePermission('incident.assign');

        // Fetch verified incidents (or those already assigned to allow reassignment)
        // Note: For a real app, pagination and filtering are needed here
        $incidents = $this->incidentRepo->search(['status' => 'verified'], 50, 0);
        
        // Let's also fetch currently assigned ones that are not resolved
        $assignedIncidents = $this->incidentRepo->search(['status' => 'assigned'], 50, 0);
        $allIncidents = array_merge($incidents, $assignedIncidents);

        // Fetch available officers (users with agency roles). 
        // For demonstration, grabbing a subset. In real app, filter by agency/department of the incident.
        $officers = $this->userRepo->search(['status' => 'active'], 100, 0);

        $this->view('assignments/index', [
            'pageTitle' => 'Incident Assignments',
            'incidents' => $allIncidents,
            'officers'  => $officers
        ]);
    }

    /**
     * Handle the assignment action.
     */
    public function assign(): void
    {
        $this->requireAuth();
        $this->requirePermission('incident.assign');

        $incidentUuid = $this->request->input('incident_id');
        $officerUuid = $this->request->input('officer_id');
        $comments = $this->request->input('comments');

        try {
            if (empty($incidentUuid) || empty($officerUuid)) {
                throw new ValidationException(['general' => ['Incident and Officer must be selected.']]);
            }

            // Execute assignment
            $this->routingService->assignToOfficer($incidentUuid, $officerUuid);
            
            // Advance workflow status to 'assigned'
            $this->workflow->transition(
                $incidentUuid, 
                'assigned', 
                $this->session->userId(), 
                'assign', 
                $comments
            );

            $this->redirectWithSuccess('/assignments', 'Incident successfully assigned to officer.');

        } catch (ValidationException $e) {
            $this->redirectWithError('/assignments', current($e->getErrors())[0]);
        } catch (\Throwable $e) {
            error_log("Assignment Error: " . $e->getMessage());
            $this->redirectWithError('/assignments', __('error.500_message'));
        }
    }
}
