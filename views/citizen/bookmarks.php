<?php $layout = 'base'; ?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-bookmark-star"></i> My Bookmarked Community Incidents</span>
    </div>
    <div class="card-body text-center py-5 text-muted">
        <i class="bi bi-bookmark-plus d-block mb-3" style="font-size:3rem;"></i>
        <h5>No bookmarks yet</h5>
        <p class="mb-4">Bookmark community incidents you care about to follow their progress here.</p>
        <a href="<?= url('map') ?>" class="btn btn-primary">
            <i class="bi bi-map me-2"></i> Browse Incident Map
        </a>
    </div>
</div>
