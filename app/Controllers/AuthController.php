<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Exceptions\AuthenticationException;
use App\Exceptions\ValidationException;
use App\Repositories\UserRepository;
use App\Services\AuthService;
use App\Validators\AuthValidator;

final class AuthController extends BaseController
{
    private AuthService $authService;
    private AuthValidator $validator;

    public function __construct(
        Request $request, 
        Response $response
    ) {
        parent::__construct($request, $response);
        
        $this->authService = new AuthService(new UserRepository());
        $this->validator   = new AuthValidator(new UserRepository());
    }

    // ── Login ──────────────────────────────────────────────────────────────────

    public function showLogin(): void
    {
        $this->authView('auth/login', [
            'pageTitle' => __('auth.login')
        ]);
    }

    public function login(): void
    {
        try {
            $data = $this->request->only(['identifier', 'password', 'remember']);
            
            // Basic validation
            if (empty($data['identifier']) || empty($data['password'])) {
                $this->redirectWithError('/login', __('auth.login_failed'));
                return;
            }

            $this->authService->login(
                $data['identifier'],
                $data['password'],
                $this->request->ip(),
                $this->request->userAgent()
            );

            // Rotate CSRF token after login for security
            $this->session->rotateCsrf();

            // Redirect to intended URL if exists, else dashboard
            $intended = $this->session->get('url.intended', '/dashboard');
            $this->session->forget('url.intended');
            
            $this->redirect($intended);

        } catch (AuthenticationException $e) {
            $this->redirectWithError('/login', $e->getMessage());
        } catch (\Throwable $e) {
            error_log('Login error: ' . $e->getMessage());
            $this->redirectWithError('/login', __('error.500_message'));
        }
    }

    // ── Logout ─────────────────────────────────────────────────────────────────

    public function logout(): void
    {
        $this->session->logout();
        $this->redirectWithSuccess('/login', __('auth.logout_success'));
    }

    // ── Registration (Citizens only) ───────────────────────────────────────────

    public function showRegister(): void
    {
        $this->authView('auth/register', [
            'pageTitle' => __('auth.register')
        ]);
    }

    public function register(): void
    {
        try {
            $data = $this->request->all();
            
            // 1. Validate
            $validated = $this->validator->validateRegistration($data);

            // 2. Register citizen
            $this->authService->registerCitizen($validated);

            $this->redirectWithSuccess('/login', 'Registration successful. Please log in.');

        } catch (ValidationException $e) {
            $this->session->flash('errors', $e->getErrors());
            $this->session->flash('old', $this->request->all());
            $this->redirect('/register');
        } catch (\Throwable $e) {
            error_log($e->getMessage());
            $this->redirectWithError('/register', __('error.500_message'));
        }
    }

    // ── Password Reset ─────────────────────────────────────────────────────────

    public function showForgotPassword(): void
    {
        $this->authView('auth/forgot-password', [
            'pageTitle' => __('auth.forgot_password')
        ]);
    }

    public function sendResetLink(): void
    {
        // TODO: PasswordResetService
        $this->redirectWithSuccess('/forgot-password', __('auth.password_reset_sent'));
    }

    public function showResetPassword(): void
    {
        $token = $this->request->routeParam('token');
        $this->authView('auth/reset-password', [
            'pageTitle' => __('auth.reset_password'),
            'token'     => $token
        ]);
    }

    public function resetPassword(): void
    {
        // TODO: PasswordResetService
        $this->redirectWithSuccess('/login', __('auth.password_changed'));
    }
}
