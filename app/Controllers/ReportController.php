<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\IncidentRepository;
use App\Services\ExportService;
use App\Utilities\UUIDHelper;

final class ReportController extends BaseController
{
    private ExportService $exportService;
    private IncidentRepository $incidentRepo;

    public function __construct(
        \App\Core\Request $request, 
        \App\Core\Response $response
    ) {
        parent::__construct($request, $response);
        $this->incidentRepo = new IncidentRepository();
        $this->exportService = new ExportService($this->incidentRepo);
    }

    /**
     * Download a CSV of all incidents (Admin/Supervisor).
     */
    public function exportCsv(): void
    {
        $this->requireAuth();
        
        // In a real app, require specific permissions like 'report.export'
        if (!in_array($this->session->get('user_role'), ['admin', 'super_admin', 'agency_officer'])) {
            $this->redirectWithError('/dashboard', __('error.403_message'));
            return;
        }

        try {
            $filePath = $this->exportService->generateIncidentsCsv();
            $filename = 'GCIRMS_Incidents_Export_' . date('Ymd_His') . '.csv';
            
            // Note: The Response->download method usually exits, 
            // so we might leave a temp file hanging. 
            // Register a shutdown function to clean it up.
            register_shutdown_function(function() use ($filePath) {
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            });

            $this->response->download($filePath, $filename);

        } catch (\Throwable $e) {
            error_log("CSV Export Error: " . $e->getMessage());
            $this->redirectWithError('/dashboard', __('error.500_message'));
        }
    }

    /**
     * Download Acknowledgement Receipt (Citizen).
     */
    public function downloadReceipt(): void
    {
        $this->requireAuth();
        
        $uuid = $this->request->routeParam('id');
        $binId = UUIDHelper::toBinary($uuid);
        
        $incidentData = $this->incidentRepo->findById($binId);
        
        if (!$incidentData) {
            $this->redirectWithError('/dashboard', 'Incident not found.');
            return;
        }

        // Verify Ownership or Admin rights
        $userIdBin = UUIDHelper::toBinary($this->session->userId());
        if ($incidentData['citizen_id'] !== $userIdBin && !in_array($this->session->get('user_role'), ['admin', 'super_admin'])) {
             $this->redirectWithError('/dashboard', __('error.403_message'));
             return;
        }

        try {
            $filePath = $this->exportService->generateAcknowledgementPdf($incidentData);
            
            // It's technically HTML for now but we will serve it as an HTML download
            $filename = 'Receipt_' . $incidentData['reference_number'] . '.html';
            
            register_shutdown_function(function() use ($filePath) {
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            });

            // If we had TCPDF installed, this would be application/pdf
            $this->response->download($filePath, $filename);

        } catch (\Throwable $e) {
            error_log("Receipt Generation Error: " . $e->getMessage());
            $this->redirectWithError('/dashboard', __('error.500_message'));
        }
    }
}
