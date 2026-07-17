<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\FileUploadException;
use App\Exceptions\ValidationException;
use App\Models\Incident;
use App\Repositories\AttachmentRepository;
use App\Repositories\CategoryRepository;
use App\Repositories\IncidentRepository;
use App\Services\FileUploadService;
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
    }

    /**
     * Lazy-load repositories to avoid constructor coupling.
     */
    private function getIncidentRepo(): IncidentRepository
    {
        $this->incidentRepo ??= new IncidentRepository();
        return $this->incidentRepo;
    }

    private function getCategoryRepo(): CategoryRepository
    {
        $this->categoryRepo ??= new CategoryRepository();
        return $this->categoryRepo;
    }

    private function getIncidentService(): IncidentService
    {
        $this->incidentService ??= new IncidentService(
            $this->getIncidentRepo(),
            $this->getCategoryRepo()
        );
        return $this->incidentService;
    }

    /**
     * List all incidents (role-aware).
     */
    public function index(): void
    {
        $this->requireAuth();

        $roleSlug = $this->session->get('user_role', 'user');
        $permissions = $this->session->get('permissions', []);

        if ($roleSlug === 'super_admin' || in_array('*', $permissions, true)) {
            $status = $this->request->query('status', '');
            $page = max(1, $this->request->int('page', 1));
            $limit = 20;
            $offset = ($page - 1) * $limit;

            $incidents = $this->getIncidentRepo()->findFiltered($status, $limit, $offset);
        } else {
            $this->redirect('/incidents/my');
            return;
        }

        $this->view('incidents/list', [
            'pageTitle' => __('nav.incidents'),
            'incidents' => $incidents,
            'status'    => $status
        ]);
    }

    /**
     * Show list of user's own incidents.
     */
    public function indexMy(): void
    {
        $this->requireAuth();
        
        $userId = $this->session->userId();
        $binUserId = UUIDHelper::toBinary($userId);
        
        $incidents = $this->getIncidentRepo()->findByCitizen($binUserId);
        
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
        
        $categories = $this->getCategoryRepo()->findActive();
        
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
            
            $incident = $this->getIncidentService()->reportIncident($data, $userId);
            
            if (!empty($_FILES['attachments']['name'][0])) {
                $fileService = new FileUploadService();
                $attRepo = new AttachmentRepository();
                
                try {
                    $uploadedFiles = $fileService->handleUpload($_FILES['attachments']);
                    
                    foreach ($uploadedFiles as $fileData) {
                        $attRepo->create([
                            'id'            => UUIDHelper::toBinary(UUIDHelper::generate()),
                            'entity_type'   => 'incident',
                            'entity_id'     => UUIDHelper::toBinary($incident->getUuid()),
                            'uploader_id'   => UUIDHelper::toBinary($userId),
                            'original_name' => $fileData['original_name'],
                            'stored_name'   => $fileData['stored_name'],
                            'file_path'     => $fileData['file_path'],
                            'mime_type'     => $fileData['mime_type'],
                            'file_size'     => $fileData['file_size'],
                            'is_image'      => $fileData['is_image'] ? 1 : 0
                        ]);
                    }
                } catch (FileUploadException $fe) {
                    error_log("Attachment Error for Incident {$incident->getReferenceNumber()}: " . $fe->getMessage());
                    $this->session->flash('errors', ['attachments' => [$fe->getMessage()]]);
                }
            }
            
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
     * Show incident drafts (stub — returns list like indexMy).
     */
    public function drafts(): void
    {
        $this->indexMy();
    }

    /**
     * Show a specific incident details.
     */
    public function show(): void
    {
        $this->requireAuth();
        
        $uuid = $this->request->routeParam('id');
        $binId = UUIDHelper::toBinary($uuid);
        
        $incidentData = $this->getIncidentRepo()->findById($binId);
        
        if (!$incidentData) {
            $this->redirectWithError('/dashboard', __('incident.not_found'));
            return;
        }

        $userIdBin = UUIDHelper::toBinary($this->session->userId());
        $permissions = $this->session->get('permissions', []);
        if ($incidentData['citizen_id'] !== $userIdBin && !in_array('incident.view', $permissions, true) && !in_array('*', $permissions, true)) {
             $this->redirectWithError('/dashboard', __('error.403_message'));
             return;
        }
        
        $category = $this->getCategoryRepo()->findById($incidentData['category_id']);
        $incidentData['category_name'] = $category['name'] ?? 'Unknown';

        $incident = new Incident($incidentData);
        
        $this->view('incidents/show', [
            'pageTitle' => $incident->getReferenceNumber(),
            'incident'  => $incident
        ]);
    }
}
