<?php $layout = 'base'; ?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-tags"></i> Incident Categories</span>
        <button class="btn btn-primary btn-sm" disabled><i class="bi bi-plus-circle"></i> Add Category</button>
    </div>
    <div class="table-responsive">
        <table class="table table-hover table-gcirms mb-0">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Parent Category</th>
                    <th>SLA (hours)</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="5" class="text-center py-4 text-muted">
                        <i class="bi bi-inbox d-block mb-2" style="font-size: 2rem;"></i>
                        Category management will be available in the next update.
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
