<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\ValidationException;
use App\Repositories\IncidentRepository;
use App\Services\WorkflowEngineService;

final class VerificationController extends BaseController
{
    private IncidentRepository $incidentRepo;
    private WorkflowEngineService $workflow;

    public function __construct(
        \App\Core\Request $request, 
        \App\Core\Response $response
    ) {
        parent::__construct($request, $response);
        
        $this->incidentRepo = new IncidentRepository();
        // Assume dependencies are resolved via a container or instantiated here manually
        $this->workflow = new WorkflowEngineService(
            $this->incidentRepo, 
            new \App\Repositories\WorkflowLogRepository()
        );
    }

    /**
     * Display the queue of incidents awaiting verification.
     */
    public function queue(): void
    {
        $this->requireAuth();
        $this->requirePermission('incident.verify');

        // Fetch incidents with status 'submitted'
        // In a real scenario, we might also filter by agency_id if the verifier is agency-specific
        $incidents = $this->incidentRepo->search(['status' => 'submitted'], 50, 0);

        $this->view('verification/queue', [
            'pageTitle' => 'Verification Queue',
            'incidents' => $incidents
        ]);
    }

    /**
     * Handle the verification action (Approve/Reject).
     */
    public function process(): void
    {
        $this->requireAuth();
        $this->requirePermission('incident.verify');

        $incidentUuid = $this->request->input('incident_id');
        $action = $this->request->input('action'); // 'approve' or 'reject'
        $comments = $this->request->input('comments');

        try {
            if ($action === 'approve') {
                $this->workflow->transition(
                    $incidentUuid, 
                    'verified', 
                    $this->session->userId(), 
                    'verify', 
                    $comments
                );
                
                // Trigger auto-routing (Phase 3 logic)
                $routingService = new \App\Services\RoutingEngineService($this->incidentRepo);
                $routingService->autoRoute($incidentUuid);

                $this->redirectWithSuccess('/verification', 'Incident verified successfully and routed.');
            } elseif ($action === 'reject') {
                if (empty($comments)) {
                    throw new ValidationException(['comments' => ['Reason is required for rejection.']]);
                }
                $this->workflow->transition(
                    $incidentUuid, 
                    'rejected', 
                    $this->session->userId(), 
                    'reject', 
                    $comments
                );
                $this->redirectWithSuccess('/verification', 'Incident rejected.');
            } else {
                throw new ValidationException(['general' => ['Invalid action.']]);
            }
        } catch (ValidationException $e) {
            $this->redirectWithError('/verification', current($e->getErrors())[0]);
        } catch (\Throwable $e) {
            error_log("Verification Error: " . $e->getMessage());
            $this->redirectWithError('/verification', __('error.500_message'));
        }
    }
}
