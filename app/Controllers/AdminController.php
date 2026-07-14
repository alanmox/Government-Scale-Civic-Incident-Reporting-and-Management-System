<?php

declare(strict_types=1);

namespace App\Controllers;

/**
 * AdminController — system administration stubs.
 * Full CRUD implementations planned for Phase 9.
 */
final class AdminController extends BaseController
{
    public function users(): void
    {
        $this->requireAuth();
        $this->view('admin/users', ['pageTitle' => 'User Management', 'breadcrumbs' => [['label' => 'Administration'], ['label' => 'Users']]]);
    }

    public function roles(): void
    {
        $this->requireAuth();
        $this->view('admin/roles', ['pageTitle' => 'Roles & Permissions', 'breadcrumbs' => [['label' => 'Administration'], ['label' => 'Roles']]]);
    }

    public function agencies(): void
    {
        $this->requireAuth();
        $this->view('admin/agencies', ['pageTitle' => 'Agencies', 'breadcrumbs' => [['label' => 'Administration'], ['label' => 'Agencies']]]);
    }

    public function categories(): void
    {
        $this->requireAuth();
        $this->view('admin/categories', ['pageTitle' => 'Incident Categories', 'breadcrumbs' => [['label' => 'Administration'], ['label' => 'Categories']]]);
    }

    public function workflow(): void
    {
        $this->requireAuth();
        $this->view('admin/workflow', ['pageTitle' => 'Workflow Configuration', 'breadcrumbs' => [['label' => 'Administration'], ['label' => 'Workflow']]]);
    }

    public function routing(): void
    {
        $this->requireAuth();
        $this->view('admin/routing', ['pageTitle' => 'Routing Rules', 'breadcrumbs' => [['label' => 'Administration'], ['label' => 'Routing']]]);
    }

    public function settings(): void
    {
        $this->requireAuth();
        $this->view('admin/settings', ['pageTitle' => 'System Settings', 'breadcrumbs' => [['label' => 'Administration'], ['label' => 'Settings']]]);
    }

    /** SLA Management — Phase 9 */
    public function sla(): void
    {
        $this->requireAuth();
        
        $slaRepo = new \App\Repositories\SlaRepository();
        $slaService = new \App\Services\SlaService($slaRepo);
        
        // We also need categories for the dropdown
        $categoryRepo = new \App\Repositories\CategoryRepository();
        $categories = $categoryRepo->getAll();

        $this->view('admin/sla', [
            'pageTitle'   => 'SLA Management',
            'breadcrumbs' => [['label' => 'Administration'], ['label' => 'SLA Management']],
            'slas'        => $slaService->getAllSlas(),
            'categories'  => $categories
        ]);
    }

    public function storeSla(): void
    {
        $this->requireAuth();
        $this->requireCsrf();
        
        $userId = $this->session->get('user_bin_id');
        if (!$userId) {
            $this->redirect('/login');
        }

        $slaRepo = new \App\Repositories\SlaRepository();
        $slaService = new \App\Services\SlaService($slaRepo);

        try {
            $slaService->saveSla(bin2hex($userId), $_POST);
            $this->session->setFlash('success', 'SLA definition saved successfully.');
        } catch (\Exception $e) {
            $this->session->setFlash('error', $e->getMessage());
        }

        $this->redirect('/admin/sla');
    }

    public function deleteSla(): void
    {
        $this->requireAuth();
        $this->requireCsrf();

        $userId = $this->session->get('user_bin_id');
        if (!$userId) {
            $this->redirect('/login');
        }

        $id = $_POST['id'] ?? '';
        if ($id) {
            $slaRepo = new \App\Repositories\SlaRepository();
            $slaService = new \App\Services\SlaService($slaRepo);
            $slaService->deleteSla(bin2hex($userId), $id);
            $this->session->setFlash('success', 'SLA definition deleted.');
        }

        $this->redirect('/admin/sla');
    }

    /** System Backup — Phase 9 */
    public function backup(): void
    {
        $this->requireAuth();
        
        $backupService = new \App\Services\BackupService();
        $backups = $backupService->getBackups();

        $this->view('admin/backup', [
            'pageTitle'   => 'System Backup',
            'breadcrumbs' => [['label' => 'Administration'], ['label' => 'System Backup']],
            'backups'     => $backups
        ]);
    }

    public function createBackup(): void
    {
        $this->requireAuth();
        $this->requireCsrf();

        $backupService = new \App\Services\BackupService();
        try {
            $backupService->createDatabaseBackup();
            $this->session->setFlash('success', 'System backup completed successfully.');
        } catch (\Exception $e) {
            $this->session->setFlash('error', $e->getMessage());
        }

        $this->redirect('/admin/backup');
    }

    public function downloadBackup(): void
    {
        $this->requireAuth();
        
        $filename = $_GET['file'] ?? '';
        if (!$filename) {
            $this->redirect('/admin/backup');
        }

        $backupService = new \App\Services\BackupService();
        $filepath = $backupService->getBackupPath($filename);

        if (!$filepath) {
            $this->session->setFlash('error', 'Backup file not found.');
            $this->redirect('/admin/backup');
        }

        // Send file to browser
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($filepath) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filepath));
        readfile($filepath);
        exit;
    }
}
