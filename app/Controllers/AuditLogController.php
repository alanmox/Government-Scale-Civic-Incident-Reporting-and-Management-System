<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\WorkflowLogRepository;

final class AuditLogController extends BaseController
{
    private WorkflowLogRepository $wfLogRepo;

    public function __construct(
        \App\Core\Request $request,
        \App\Core\Response $response
    ) {
        parent::__construct($request, $response);
        $this->wfLogRepo = new WorkflowLogRepository();
    }

    public function index(): void
    {
        $this->requireAuth();

        $page   = max(1, $this->request->int('page', 1));
        $limit  = 50;
        $offset = ($page - 1) * $limit;

        $logs  = $this->wfLogRepo->findAuditLogs($limit, $offset);
        $total = $this->wfLogRepo->countAll();

        $this->view('admin/audit_logs', [
            'pageTitle'   => 'Audit Logs',
            'breadcrumbs' => [['label' => 'Administration'], ['label' => 'Audit Logs']],
            'logs'        => $logs,
            'page'        => $page,
            'totalPages'  => (int) ceil($total / $limit)
        ]);
    }
}
