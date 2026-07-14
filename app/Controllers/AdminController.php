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
        $this->view('admin/sla', [
            'pageTitle'   => 'SLA Management',
            'breadcrumbs' => [['label' => 'Administration'], ['label' => 'SLA Management']]
        ]);
    }

    /** System Backup — Phase 9 */
    public function backup(): void
    {
        $this->requireAuth();
        $this->view('admin/backup', [
            'pageTitle'   => 'System Backup',
            'breadcrumbs' => [['label' => 'Administration'], ['label' => 'System Backup']]
        ]);
    }
}
