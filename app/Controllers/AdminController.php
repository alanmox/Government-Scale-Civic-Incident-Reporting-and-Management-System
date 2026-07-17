<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Repositories\CategoryRepository;
use App\Repositories\SlaRepository;
use App\Services\BackupService;
use App\Services\SlaService;
use App\Utilities\UUIDHelper;

final class AdminController extends BaseController
{
    private ?SlaService $slaService = null;
    private ?CategoryRepository $categoryRepo = null;
    private ?BackupService $backupService = null;

    public function __construct(Request $request, Response $response)
    {
        parent::__construct($request, $response);
    }

    private function getSlaService(): SlaService
    {
        $this->slaService ??= new SlaService(new SlaRepository());
        return $this->slaService;
    }

    private function getCategoryRepo(): CategoryRepository
    {
        $this->categoryRepo ??= new CategoryRepository();
        return $this->categoryRepo;
    }

    private function getBackupService(): BackupService
    {
        $this->backupService ??= new BackupService();
        return $this->backupService;
    }

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

    public function sla(): void
    {
        $this->requireAuth();

        $this->view('admin/sla', [
            'pageTitle'   => 'SLA Management',
            'breadcrumbs' => [['label' => 'Administration'], ['label' => 'SLA Management']],
            'slas'        => $this->getSlaService()->getAllSlas(),
            'categories'  => $this->getCategoryRepo()->findAll(),
        ]);
    }

    public function storeSla(): void
    {
        $this->requireAuth();

        $userId = $this->session->userId();
        if (!$userId) {
            $this->redirect('/login');
        }

        try {
            $this->getSlaService()->saveSla(UUIDHelper::toString($userId), $this->request->all());
            $this->session->flash('success', 'SLA definition saved successfully.');
        } catch (\Exception $e) {
            $this->session->flash('error', $e->getMessage());
        }

        $this->redirect('/admin/sla');
    }

    public function deleteSla(): void
    {
        $this->requireAuth();

        $userId = $this->session->userId();
        if (!$userId) {
            $this->redirect('/login');
        }

        $id = $this->request->input('id', '');
        if ($id) {
            $this->getSlaService()->deleteSla(UUIDHelper::toString($userId), $id);
            $this->session->flash('success', 'SLA definition deleted.');
        }

        $this->redirect('/admin/sla');
    }

    public function backup(): void
    {
        $this->requireAuth();

        $this->view('admin/backup', [
            'pageTitle'   => 'System Backup',
            'breadcrumbs' => [['label' => 'Administration'], ['label' => 'System Backup']],
            'backups'     => $this->getBackupService()->getBackups(),
        ]);
    }

    public function createBackup(): void
    {
        $this->requireAuth();

        try {
            $this->getBackupService()->createDatabaseBackup();
            $this->session->flash('success', 'System backup completed successfully.');
        } catch (\Exception $e) {
            $this->session->flash('error', $e->getMessage());
        }

        $this->redirect('/admin/backup');
    }

    public function downloadBackup(): void
    {
        $this->requireAuth();

        $filename = $this->request->query('file', '');
        if (!$filename) {
            $this->redirect('/admin/backup');
        }

        $filepath = $this->getBackupService()->getBackupPath($filename);

        if (!$filepath) {
            $this->session->flash('error', 'Backup file not found.');
            $this->redirect('/admin/backup');
        }

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
