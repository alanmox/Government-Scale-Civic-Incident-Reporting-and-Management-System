<?php $layout = 'base'; ?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header"><i class="bi bi-bell-slash"></i> Alert & Notification Preferences</div>
            <div class="card-body">
                <p class="text-muted mb-4">Choose how you want to be notified when your reports are updated.</p>
                
                <form method="POST" action="<?= url('notification-settings/save') ?>">
                    <?= csrf_field() ?>
                    
                    <h6 class="fw-bold text-primary mb-3">Notification Channels</h6>
                    <div class="mb-4">
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" id="notif_inapp" name="inapp" value="1" checked>
                            <label class="form-check-label" for="notif_inapp">
                                <i class="bi bi-bell me-2 text-primary"></i> In-App Notifications
                                <small class="text-muted d-block">Shown in the notification bell on this website.</small>
                            </label>
                        </div>
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" id="notif_email" name="email" value="1">
                            <label class="form-check-label" for="notif_email">
                                <i class="bi bi-envelope me-2 text-success"></i> Email Notifications
                                <small class="text-muted d-block">Sent to your registered email address.</small>
                            </label>
                        </div>
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" id="notif_sms" name="sms" value="1">
                            <label class="form-check-label" for="notif_sms">
                                <i class="bi bi-phone me-2 text-warning"></i> SMS Notifications
                                <small class="text-muted d-block">Sent to your registered phone number. (Requires phone number on profile)</small>
                            </label>
                        </div>
                    </div>
                    
                    <h6 class="fw-bold text-primary mb-3">Notify Me When</h6>
                    <div class="mb-4">
                        <?php $events = [
                            'on_verified'     => 'My report is verified by an officer',
                            'on_assigned'     => 'A government officer is assigned to my report',
                            'on_progress'     => 'New progress update is posted on my report',
                            'on_resolved'     => 'My report is marked as Resolved',
                            'on_rejected'     => 'My report is rejected (requires correction)',
                            'system_announcements' => 'System announcements are posted',
                        ]; ?>
                        <?php foreach ($events as $key => $label): ?>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="evt_<?= $key ?>" name="events[]" value="<?= $key ?>" checked>
                                <label class="form-check-label" for="evt_<?= $key ?>"><?= e($label) ?></label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-save me-2"></i> Save Preferences
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
