<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\SessionManager;

/**
 * Base Controller
 *
 * Provides all controllers with access to Request, Response, and Session.
 * Controllers remain thin — they call services and return responses.
 *
 * Never place business logic here.
 */
abstract class BaseController
{
    protected readonly Request $request;
    protected readonly Response $response;
    protected readonly SessionManager $session;

    public function __construct(Request $request, Response $response)
    {
        $this->request  = $request;
        $this->response = $response;
        $this->session  = SessionManager::getInstance();
    }

    // ── View Helpers ───────────────────────────────────────────────────────────

    /**
     * Renders a view with the standard base layout.
     *
     * @param array<string, mixed> $data
     */
    protected function view(string $view, array $data = [], int $status = 200): void
    {
        $data['session'] = $this->session;
        $data['layout'] ??= 'base';
        $this->response->view($view, $data, $status);
    }

    /**
     * Renders a view using the auth layout (no sidebar).
     *
     * @param array<string, mixed> $data
     */
    protected function authView(string $view, array $data = []): void
    {
        $data['layout'] = 'auth';
        $this->view($view, $data);
    }

    // ── Redirect Helpers ───────────────────────────────────────────────────────

    protected function redirect(string $url): void
    {
        $this->response->redirect($url);
    }

    protected function back(): void
    {
        $this->response->back();
    }

    protected function redirectWithSuccess(string $url, string $message): void
    {
        $this->session->flash('success', $message);
        $this->redirect($url);
    }

    protected function redirectWithError(string $url, string $message): void
    {
        $this->session->flash('error', $message);
        $this->redirect($url);
    }

    // ── JSON Helpers ───────────────────────────────────────────────────────────

    protected function json(mixed $data, int $status = 200): void
    {
        $this->response->json($data, $status);
    }

    protected function apiSuccess(mixed $data = null, string $message = 'Success', ?array $meta = null): void
    {
        $this->response->apiSuccess($data, $message, $meta);
    }

    protected function apiError(string $message, ?array $errors = null, int $status = 400): void
    {
        $this->response->apiError($message, $errors, $status);
    }

    // ── Authorization Helper ───────────────────────────────────────────────────

    /**
     * Aborts with 403 if the current user lacks the required permission.
     *
     * @throws \App\Exceptions\AuthorizationException
     */
    protected function requirePermission(string $permission): void
    {
        $permissions = $this->session->get('permissions', []);

        if (!in_array($permission, $permissions, true)) {
            if ($this->request->isApi()) {
                $this->apiError('Insufficient permissions.', null, 403);
                return;
            }
            $this->response->abort(403, "You do not have permission: [{$permission}]");
        }
    }

    /**
     * Aborts with 401 if the user is not logged in.
     */
    protected function requireAuth(): void
    {
        if (!$this->session->isLoggedIn()) {
            if ($this->request->isApi()) {
                $this->apiError('Authentication required.', null, 401);
                return;
            }
            $this->session->flash('error', 'Please log in to continue.');
            $this->redirect('/login');
        }
    }
}
