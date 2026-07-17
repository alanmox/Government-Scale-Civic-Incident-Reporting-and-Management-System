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

    public function markAllRead(): void
    {
        $this->requireAuth();

        $this->session->set('unread_notifications', 0);
        $this->redirect('/notifications');
    }

    public function unreadCount(): void
    {
        $this->requireAuth();

        $count = $this->session->get('unread_notifications', 0);
        $this->json(['data' => ['count' => $count]]);
    }
}
