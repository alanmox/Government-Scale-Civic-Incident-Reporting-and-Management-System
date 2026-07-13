<?php
/**
 * Flash Messages / Alerts Partial
 * Displays session flash messages then clears them.
 * @var \App\Core\SessionManager $session
 */
$types = ['success' => 'success', 'error' => 'danger', 'warning' => 'warning', 'info' => 'info'];

foreach ($types as $flashType => $bsClass):
    $messages = $session->getFlash($flashType);
    foreach ($messages as $message):
?>
<div class="alert alert-<?= $bsClass ?> d-flex align-items-center gap-2 mb-3 alert-dismissible fade show" role="alert">
    <?php
    $icons = ['success' => 'check-circle-fill', 'danger' => 'exclamation-triangle-fill',
              'warning' => 'exclamation-triangle-fill', 'info' => 'info-circle-fill'];
    ?>
    <i class="bi bi-<?= $icons[$bsClass] ?? 'info-circle-fill' ?> flex-shrink-0"></i>
    <span><?= e($message) ?></span>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php
    endforeach;
endforeach;
?>
