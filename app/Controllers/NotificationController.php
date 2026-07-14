<?php

declare(strict_types=1);

namespace App\Controllers;

final class NotificationController extends BaseController
{
    public function index(): void
    {
        $this->requireAuth();

        $this->view('notifications/list', [
            'pageTitle' => __('nav.notifications'),
        ]);
    }
}
