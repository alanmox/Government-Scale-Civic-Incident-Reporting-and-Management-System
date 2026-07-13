<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\SessionManager;
use App\Exceptions\AuthenticationException;
use App\Models\SuperAdmin;
use App\Models\User;
use App\Repositories\UserRepository;
use App\Utilities\PasswordHasher;
use App\Utilities\UUIDHelper;

/**
 * Authentication Service
 *
 * Handles login, lockout enforcement, session initiation, and permission loading.
 */
final class AuthService extends BaseService
{
    private UserRepository $userRepo;

    public function __construct(UserRepository $userRepo)
    {
        parent::__construct();
        $this->userRepo = $userRepo;
    }

    /**
     * Attempts to authenticate a user by email/username and password.
     *
     * @throws AuthenticationException On failure or lockout.
     */
    public function login(string $identifier, string $password, string $ip, string $userAgent): User
    {
        $userData = $this->userRepo->findByEmailOrUsername($identifier);

        if (!$userData) {
            $this->logFailure(null, $ip, $userAgent, 'user_not_found');
            throw new AuthenticationException(__('auth.login_failed'));
        }

        $user = new User($userData);
        $userId = $user->getId();

        // Check if locked
        if ($user->isLockedOut()) {
            $this->logFailure($userId, $ip, $userAgent, 'account_locked');
            throw new AuthenticationException(__('auth.account_locked'));
        }

        // Verify password
        if (!PasswordHasher::verify($password, $userData['password_hash'])) {
            $attempts = $this->userRepo->incrementFailedAttempts($userId);
            
            $maxAttempts = (int) config('app.password.max_attempts', 5);
            if ($attempts >= $maxAttempts) {
                $lockMinutes = (int) config('app.password.lockout_minutes', 15);
                $this->userRepo->lockAccount($userId, $lockMinutes);
                $this->logFailure($userId, $ip, $userAgent, 'max_attempts_reached');
                throw new AuthenticationException(__('auth.account_locked'));
            }

            $this->logFailure($userId, $ip, $userAgent, 'invalid_password');
            throw new AuthenticationException(__('auth.login_failed'));
        }

        // Verify active status (allow if pending_verification to show resend prompt)
        if ($user->isSuspended() || $user->getStatus() === 'inactive') {
            $this->logFailure($userId, $ip, $userAgent, 'account_inactive');
            throw new AuthenticationException(__('auth.account_inactive'));
        }

        // Success!
        $this->userRepo->resetFailedAttempts($userId);
        $this->userRepo->recordLogin($userId, $ip);

        $this->userRepo->recordLoginAttempt([
            'id'             => UUIDHelper::toBinary(UUIDHelper::generate()),
            'user_id'        => $userId,
            'ip_address'     => $ip,
            'user_agent'     => $userAgent,
            'status'         => 'success',
            'failure_reason' => null,
        ]);

        // Load roles and permissions into session
        $this->initializeSession($user);

        return $user;
    }

    /**
     * Loads user roles and permissions, storing them in the session.
     */
    private function initializeSession(User $user): void
    {
        $userId = $user->getId();
        
        $roles = $this->userRepo->findUserRoles($userId);
        $roleId = $roles[0]['id'] ?? null; // Primary role
        $roleName = $roles[0]['name'] ?? 'User';

        $permissions = [];
        if ($user instanceof SuperAdmin) {
            // SuperAdmin bypass (checked in middleware if we want, but storing a flag is easier)
            $permissions = ['*']; 
        } else {
            $permissions = $this->userRepo->findUserPermissions($userId);
        }

        $this->session->login($userId, $roleId ?? '', [
            'permissions' => $permissions,
            'user_info'   => [
                'name'   => $user->getFullName(),
                'email'  => $user->getEmail(),
                'avatar' => $user->getProfilePhotoUrl(),
                'role'   => $roleName
            ]
        ]);
    }

    private function logFailure(?string $userId, string $ip, string $userAgent, string $reason): void
    {
        if ($userId) {
            $this->userRepo->recordLoginAttempt([
                'id'             => UUIDHelper::toBinary(UUIDHelper::generate()),
                'user_id'        => $userId,
                'ip_address'     => $ip,
                'user_agent'     => $userAgent,
                'status'         => 'failed',
                'failure_reason' => $reason,
            ]);
        } else {
            // Log anonymous failure to error log
            error_log(sprintf('[Auth Failure] IP: %s, Reason: %s', $ip, $reason));
        }
    }
}
