<?php
$layout = 'base';
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-briefcase"></i> My Work Orders</span>
        <div class="dropdown">
            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                Filter Status
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="?status=all">All</a></li>
                <li><a class="dropdown-item" href="?status=pending">Pending</a></li>
                <li><a class="dropdown-item" href="?status=in_progress">In Progress</a></li>
                <li><a class="dropdown-item" href="?status=completed">Completed</a></li>
            </ul>
        </div>
    </div>
    
    <div class="table-responsive">
        <table class="table table-hover table-gcirms mb-0">
            <thead>
                <tr>
                    <th>WO Ref</th>
                    <th>Incident Ref</th>
                    <th>Title</th>
                    <th>Assigned On</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($workOrders)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">
                            <i class="bi bi-inbox d-block mb-2" style="font-size: 2rem;"></i>
                            You have no assigned work orders.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($workOrders as $woData): 
                        $wo = new \App\Models\WorkOrder($woData);
                    ?>
                        <tr>
                            <td class="fw-600">
                                <a href="<?= url("work-orders/{$wo->getUuid()}") ?>" style="text-decoration: none;">
                                    <?= e($wo->getReferenceNumber()) ?>
                                </a>
                            </td>
                            <td>
                                <a href="<?= url("incidents/{$woData['incident_uuid_str']}") ?>" class="text-muted" style="text-decoration: none; font-size: .85rem;">
                                    <i class="bi bi-link-45deg"></i> <?= e($woData['incident_ref']) ?>
                                </a>
                            </td>
                            <td><?= e(strlen($wo->getTitle()) > 30 ? substr($wo->getTitle(), 0, 30) . '...' : $wo->getTitle()) ?></td>
                            <td><?= e(date('M d, Y', strtotime($wo->getCreatedAt()))) ?></td>
                            <td>
                                <span class="<?= $wo->getStatusBadgeClass() ?>">
                                    <?= e(ucwords(str_replace('_', ' ', $wo->getStatus()))) ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="<?= url("work-orders/{$wo->getUuid()}") ?>" class="btn btn-sm btn-primary">
                                    Manage
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
