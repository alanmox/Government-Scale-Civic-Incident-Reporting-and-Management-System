<?php $layout = 'base'; ?>

<div class="row">
    <!-- Form -->
    <div class="col-lg-4 mb-4">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-plus-circle"></i> Add / Edit SLA Definition</div>
            <div class="card-body">
                <form action="<?= url('admin/sla') ?>" method="POST">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label">Category <span class="required">*</span></label>
                        <select name="category_id" class="form-select" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= e($cat['id']) ?>"><?= e($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Priority <span class="required">*</span></label>
                        <select name="priority" class="form-select" required>
                            <option value="critical">Critical</option>
                            <option value="high">High</option>
                            <option value="medium">Medium</option>
                            <option value="low">Low</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Resolution Target (Hours) <span class="required">*</span></label>
                        <input type="number" name="resolve_hours" class="form-control" required min="1" max="8760" value="72">
                        <div class="form-text">Target time to completely resolve the incident.</div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Escalation Warning (Hours) <span class="required">*</span></label>
                        <input type="number" name="escalate_hours" class="form-control" required min="1" max="8760" value="48">
                        <div class="form-text">Time before an automatic escalation warning is triggered.</div>
                    </div>
                    <button class="btn btn-primary w-100"><i class="bi bi-save"></i> Save SLA Definition</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="col-lg-8 mb-4">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-clock-history"></i> Current SLA Definitions</div>
            <div class="table-responsive">
                <table class="table table-hover table-gcirms mb-0">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Priority</th>
                            <th>Resolve Target</th>
                            <th>Escalation Warning</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($slas)): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">No custom SLAs defined. System defaults will be used.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($slas as $sla): ?>
                                <tr>
                                    <td class="fw-bold"><?= e($sla['category_name']) ?></td>
                                    <td><?= badge_for_status($sla['priority']) ?></td>
                                    <td><?= e($sla['resolve_hours']) ?> hours</td>
                                    <td><?= e($sla['escalate_hours']) ?> hours</td>
                                    <td>
                                        <form action="<?= url('admin/sla/delete') ?>" method="POST" onsubmit="return confirm('Delete this SLA definition?');" class="d-inline">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="id" value="<?= e($sla['id']) ?>">
                                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
