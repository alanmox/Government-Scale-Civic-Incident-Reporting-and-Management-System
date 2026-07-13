<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\ValidationException;
use App\Repositories\CategoryRepository;
use App\Repositories\IncidentRepository;
use App\Services\IncidentService;
use App\Utilities\UUIDHelper;

final class IncidentController extends BaseController
{
    private IncidentService $incidentService;
    private IncidentRepository $incidentRepo;
    private CategoryRepository $categoryRepo;

    public function __construct(
        \App\Core\Request $request, 
        \App\Core\Response $response
    ) {
        parent::__construct($request, $response);
        
        $this->incidentRepo = new IncidentRepository();
        $this->categoryRepo = new CategoryRepository();
        $this->incidentService = new IncidentService($this->incidentRepo, $this->categoryRepo);
    }

    /**
     * Show list of user's own incidents.
     */
    public function indexMy(): void
    {
        $this->requireAuth();
        
        $userId = $this->session->userId();
        $binUserId = UUIDHelper::toBinary($userId);
        
        $incidents = $this->incidentRepo->findByCitizen($binUserId);
        
        $this->view('incidents/my_list', [
            'pageTitle' => __('nav.my_reports'),
            'incidents' => $incidents
        ]);
    }

    /**
     * Show incident reporting form.
     */
    public function create(): void
    {
        $this->requireAuth();
        
        $categories = $this->categoryRepo->findActive();
        
        $this->view('incidents/create', [
            'pageTitle' => __('incident.report'),
            'categories' => $categories
        ]);
    }

    /**
     * Handle incident submission.
     */
    public function store(): void
    {
        $this->requireAuth();
        
        try {
            $data = $this->request->all();
            $userId = $this->session->userId();
            
            $incident = $this->incidentService->reportIncident($data, $userId);
            
            $this->redirectWithSuccess('/incidents/my', __('incident.submitted', ['number' => $incident->getReferenceNumber()]));
            
        } catch (ValidationException $e) {
            $this->session->flash('errors', $e->getErrors());
            $this->session->flash('old', $this->request->all());
            $this->redirect('/incidents/create');
        } catch (\Throwable $e) {
            error_log("Incident Report Error: " . $e->getMessage());
            $this->redirectWithError('/incidents/create', __('error.500_message'));
        }
    }

    /**
     * Show a specific incident details.
     */
    public function show(): void
    {
        $this->requireAuth();
        
        $uuid = $this->request->routeParam('id');
        $binId = UUIDHelper::toBinary($uuid);
        
        $incidentData = $this->incidentRepo->findById($binId);
        
        if (!$incidentData) {
            $this->redirectWithError('/dashboard', __('incident.not_found'));
            return;
        }

        // Simplistic authorization check for demonstration
        $userIdBin = UUIDHelper::toBinary($this->session->userId());
        if ($incidentData['citizen_id'] !== $userIdBin && !$this->session->hasPermission('incident.view')) {
             $this->redirectWithError('/dashboard', __('error.403_message'));
             return;
        }
        
        // Also fetch category details for display
        $category = $this->categoryRepo->findById($incidentData['category_id']);
        $incidentData['category_name'] = $category['name'] ?? 'Unknown';

        $incident = new \App\Models\Incident($incidentData);
        
        $this->view('incidents/show', [
            'pageTitle' => $incident->getReferenceNumber(),
            'incident'  => $incident
        ]);
    }
}
