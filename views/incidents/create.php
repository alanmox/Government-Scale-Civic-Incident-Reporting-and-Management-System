<?php
$layout = 'base';
$old = $session->getFlash('old')[0] ?? [];
$errors = $session->getFlash('errors')[0] ?? [];
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-file-earmark-plus"></i>
                <?= e(__('incident.report')) ?>
            </div>
            <div class="card-body">
                
                <div class="step-wizard d-none d-md-flex">
                    <div class="step-item active">
                        <div class="step-circle">1</div>
                        <div class="step-label">Details</div>
                    </div>
                    <div class="step-item">
                        <div class="step-circle">2</div>
                        <div class="step-label">Location</div>
                    </div>
                    <div class="step-item">
                        <div class="step-circle">3</div>
                        <div class="step-label">Evidence</div>
                    </div>
                </div>

                <form action="<?= url('incidents') ?>" method="POST" enctype="multipart/form-data" id="incident-form">
                    <?= csrf_field() ?>

                    <h5 class="mb-3 text-primary" style="font-size: 1rem; font-weight: 600;">1. Incident Details</h5>

                    <div class="mb-3">
                        <label class="form-label" for="category_id"><?= e(__('incident.category')) ?> <span class="required">*</span></label>
                        <select class="form-select <?= isset($errors['category_id']) ? 'is-invalid' : '' ?>" 
                                id="category_id" name="category_id" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= e($cat['id']) ?>" <?= ($old['category_id'] ?? '') === $cat['id'] ? 'selected' : '' ?>>
                                    <?= e($cat['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($errors['category_id'])): ?>
                            <div class="invalid-feedback"><?= e($errors['category_id'][0]) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="title"><?= e(__('incident.title')) ?> <span class="required">*</span></label>
                        <input type="text" class="form-control <?= isset($errors['title']) ? 'is-invalid' : '' ?>" 
                               id="title" name="title" value="<?= e($old['title'] ?? '') ?>" 
                               placeholder="Brief description of the issue" required>
                        <?php if (isset($errors['title'])): ?>
                            <div class="invalid-feedback"><?= e($errors['title'][0]) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-4">
                        <label class="form-label" for="description"><?= e(__('incident.description')) ?> <span class="required">*</span></label>
                        <textarea class="form-control <?= isset($errors['description']) ? 'is-invalid' : '' ?>" 
                                  id="description" name="description" rows="4" 
                                  placeholder="Provide detailed information..." required><?= e($old['description'] ?? '') ?></textarea>
                        <?php if (isset($errors['description'])): ?>
                            <div class="invalid-feedback"><?= e($errors['description'][0]) ?></div>
                        <?php endif; ?>
                    </div>

                    <hr class="my-4">
                    <h5 class="mb-3 text-primary" style="font-size: 1rem; font-weight: 600;">2. Location Details</h5>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="region_id">Region</label>
                            <select class="form-select" id="region_id" name="region_id">
                                <option value="">Select Region</option>
                                <!-- Populated via AJAX in Phase 6 -->
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="district_id">District</label>
                            <select class="form-select" id="district_id" name="district_id" disabled>
                                <option value="">Select District</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="ward_id">Ward</label>
                            <select class="form-select" id="ward_id" name="ward_id" disabled>
                                <option value="">Select Ward</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="village_id">Village / Street</label>
                            <select class="form-select" id="village_id" name="village_id" disabled>
                                <option value="">Select Village</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="location_desc">Specific Location Details</label>
                        <input type="text" class="form-control" id="location_desc" name="location_desc" 
                               value="<?= e($old['location_desc'] ?? '') ?>" 
                               placeholder="e.g. Near the main bus stand">
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Map Coordinates (Optional)</label>
                        <div class="map-container mb-2">
                            <div id="incident-map"></div>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-locate">
                                <i class="bi bi-geo-alt me-1"></i> Get My Location
                            </button>
                            <input type="text" class="form-control form-control-sm" id="latitude" name="latitude" 
                                   value="<?= e($old['latitude'] ?? '') ?>" placeholder="Latitude" readonly>
                            <input type="text" class="form-control form-control-sm" id="longitude" name="longitude" 
                                   value="<?= e($old['longitude'] ?? '') ?>" placeholder="Longitude" readonly>
                        </div>
                    </div>

                    <hr class="my-4">
                    <h5 class="mb-3 text-primary" style="font-size: 1rem; font-weight: 600;">3. Attachments & Settings</h5>

                    <div class="mb-4">
                        <label class="form-label" for="attachments">Evidence (Photos/Documents)</label>
                        <input class="form-control" type="file" id="attachments" name="attachments[]" multiple accept="image/*,.pdf,.doc,.docx">
                        <div class="form-text">Max 10MB total. Formats: JPG, PNG, PDF, DOCX.</div>
                    </div>

                    <div class="mb-4 form-check">
                        <input type="checkbox" class="form-check-input" id="is_public" name="is_public" value="1" <?= !empty($old['is_public']) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_public">Allow this incident to be visible on public portals</label>
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <span id="draft-status" class="text-muted" style="font-size: .8rem;"></span>
                        <div>
                            <a href="<?= url('dashboard') ?>" class="btn btn-outline-secondary me-2"><?= e(__('cancel')) ?></a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-send me-1"></i> <?= e(__('submit')) ?>
                            </button>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<?php ob_start(); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    initLocationPicker('incident-map', 'latitude', 'longitude');
    // initAutoSave('incident-form', '/api/v1/incidents/draft');
});
</script>
<?php $extraJs = ob_get_clean(); ?>
