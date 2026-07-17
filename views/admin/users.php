<?php $layout = 'base'; ?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-people"></i> User Management</span>
        <button class="btn btn-primary btn-sm" disabled><i class="bi bi-plus-circle"></i> Add User</button>
    </div>
    <div class="table-responsive">
        <table class="table table-hover table-gcirms mb-0">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Registered</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="6" class="text-center py-4 text-muted">
                        <i class="bi bi-inbox d-block mb-2" style="font-size: 2rem;"></i>
                        User list will be available in the next update.
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
