<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Database\Connection;
use App\Interfaces\Searchable;
use PDO;

/**
 * User Repository
 *
 * All SQL for the `users`, `user_roles`, `login_history`, `password_resets`,
 * and `password_history` tables lives here.
 */
final class UserRepository extends BaseRepository implements Searchable
{
    protected string $table      = 'users';
    protected string $primaryKey = 'id';
    protected bool   $softDeletes = true;

    // ── Lookups ────────────────────────────────────────────────────────────────

    /**
     * Finds a user by email address (used for login).
     *
     * @return array<string, mixed>|null
     */
    public function findByEmail(string $email): ?array
    {
        $sql = 'SELECT u.*, BIN_TO_UUID(u.id) AS uuid_str
                FROM `users` u
                WHERE u.email = :email
                  AND u.deleted_at IS NULL
                LIMIT 1';

        $stmt = $this->execute($sql, ['email' => $email]);
        $row  = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Finds a user by username.
     *
     * @return array<string, mixed>|null
     */
    public function findByUsername(string $username): ?array
    {
        $sql = 'SELECT u.*, BIN_TO_UUID(u.id) AS uuid_str
                FROM `users` u
                WHERE u.username = :username
                  AND u.deleted_at IS NULL
                LIMIT 1';

        $stmt = $this->execute($sql, ['username' => $username]);
        $row  = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Finds by email or username (login supports both).
     *
     * @return array<string, mixed>|null
     */
    public function findByEmailOrUsername(string $identifier): ?array
    {
        $sql = 'SELECT u.*, BIN_TO_UUID(u.id) AS uuid_str
                FROM `users` u
                WHERE (u.email = :identifier OR u.username = :identifier)
                  AND u.deleted_at IS NULL
                LIMIT 1';

        $stmt = $this->execute($sql, ['identifier' => $identifier]);
        $row  = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Finds a user by email verification token.
     *
     * @return array<string, mixed>|null
     */
    public function findByVerifyToken(string $token): ?array
    {
        $row = $this->findOneWhere(['email_verify_token' => $token]);
        return $row;
    }

    // ── Role Loading ───────────────────────────────────────────────────────────

    /**
     * Returns all roles assigned to a user (for session permission loading).
     *
     * @return list<array<string, mixed>>
     */
    public function findUserRoles(string $userId): array
    {
        $sql = 'SELECT r.id, r.name, r.slug
                FROM `roles` r
                INNER JOIN `user_roles` ur ON ur.role_id = r.id
                WHERE ur.user_id = :userId
                  AND r.deleted_at IS NULL
                ORDER BY r.name';

        return $this->execute($sql, ['userId' => $userId])->fetchAll();
    }

    /**
     * Returns all permission slugs for a user (aggregated across all roles).
     *
     * @return string[]
     */
    public function findUserPermissions(string $userId): array
    {
        $sql = 'SELECT DISTINCT p.slug
                FROM `permissions` p
                INNER JOIN `role_permissions` rp ON rp.permission_id = p.id
                INNER JOIN `user_roles` ur ON ur.role_id = rp.role_id
                WHERE ur.user_id = :userId
                ORDER BY p.slug';

        $rows = $this->execute($sql, ['userId' => $userId])->fetchAll(PDO::FETCH_COLUMN);
        return $rows ?: [];
    }

    /**
     * Find a role ID by slug.
     */
    public function findRoleBySlug(string $slug): ?array
    {
        $sql = 'SELECT id, name, slug FROM roles WHERE slug = :slug AND deleted_at IS NULL LIMIT 1';
        $stmt = $this->execute($sql, ['slug' => $slug]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Creates a new user record.
     */
    public function createUser(array $data): void
    {
        $columns = ['id', 'uuid', 'full_name', 'username', 'email', 'phone', 'password_hash', 'status'];
        $cols = implode(', ', array_map(fn($c) => "`{$c}`", $columns));
        $vals = implode(', ', array_map(fn($c) => ":{$c}", $columns));

        $sql = "INSERT INTO `{$this->table}` ({$cols}) VALUES ({$vals})";
        $this->execute($sql, $data);
    }

    /**
     * Assigns a role to a user (idempotent).
     */
    public function assignRole(string $userId, string $roleId, ?string $assignedBy = null): void
    {
        $sql = 'INSERT IGNORE INTO `user_roles` (user_id, role_id, assigned_by)
                VALUES (:userId, :roleId, :assignedBy)';

        $this->execute($sql, [
            'userId'     => $userId,
            'roleId'     => $roleId,
            'assignedBy' => $assignedBy,
        ]);
    }

    /**
     * Removes a specific role from a user.
     */
    public function revokeRole(string $userId, string $roleId): void
    {
        $sql = 'DELETE FROM `user_roles` WHERE user_id = :userId AND role_id = :roleId';
        $this->execute($sql, ['userId' => $userId, 'roleId' => $roleId]);
    }

    /**
     * Removes all roles from a user then assigns the new set.
     *
     * @param string[] $roleIds
     */
    public function syncRoles(string $userId, array $roleIds, ?string $assignedBy = null): void
    {
        $this->pdo->beginTransaction();
        try {
            $this->execute('DELETE FROM `user_roles` WHERE user_id = :userId', ['userId' => $userId]);
            foreach ($roleIds as $roleId) {
                $this->assignRole($userId, $roleId, $assignedBy);
            }
            $this->pdo->commit();
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    // ── Login History ──────────────────────────────────────────────────────────

    /**
     * Records a login attempt in login_history.
     *
     * @param array<string, mixed> $data
     */
    public function recordLoginAttempt(array $data): void
    {
        $sql = 'INSERT INTO `login_history`
                    (id, user_id, ip_address, user_agent, status, failure_reason)
                VALUES (:id, :userId, :ip, :ua, :status, :reason)';

        $this->execute($sql, [
            'id'     => $data['id'],
            'userId' => $data['user_id'],
            'ip'     => $data['ip_address'],
            'ua'     => $data['user_agent'] ?? null,
            'status' => $data['status'],
            'reason' => $data['failure_reason'] ?? null,
        ]);
    }

    /**
     * Increments the failed login counter and optionally locks the account.
     */
    public function incrementFailedAttempts(string $userId): int
    {
        $this->execute(
            'UPDATE `users` SET failed_login_attempts = failed_login_attempts + 1 WHERE id = :id',
            ['id' => $userId]
        );

        $row = $this->execute(
            'SELECT failed_login_attempts FROM `users` WHERE id = :id',
            ['id' => $userId]
        )->fetch();

        return (int) ($row['failed_login_attempts'] ?? 0);
    }

    /**
     * Locks a user account for the specified number of minutes.
     */
    public function lockAccount(string $userId, int $lockMinutes = 15): void
    {
        $until = date('Y-m-d H:i:s', time() + $lockMinutes * 60);
        $this->execute(
            "UPDATE `users` SET status = 'locked', locked_until = :until WHERE id = :id",
            ['until' => $until, 'id' => $userId]
        );
    }

    /**
     * Resets failed attempts and clears lock after successful login.
     */
    public function resetFailedAttempts(string $userId): void
    {
        $this->execute(
            "UPDATE `users`
             SET failed_login_attempts = 0,
                 locked_until = NULL,
                 status = IF(status = 'locked', 'active', status)
             WHERE id = :id",
            ['id' => $userId]
        );
    }

    /**
     * Updates last_login_at and last_login_ip.
     */
    public function recordLogin(string $userId, string $ip): void
    {
        $this->execute(
            'UPDATE `users` SET last_login_at = NOW(), last_login_ip = :ip WHERE id = :id',
            ['ip' => $ip, 'id' => $userId]
        );
    }

    // ── Password Management ────────────────────────────────────────────────────

    /**
     * Stores a password reset token hash in password_resets.
     */
    public function createPasswordReset(string $id, string $userId, string $tokenHash, string $expiresAt): void
    {
        // Invalidate previous tokens for this user
        $this->execute(
            "DELETE FROM `password_resets` WHERE user_id = :userId AND used_at IS NULL",
            ['userId' => $userId]
        );

        $this->execute(
            'INSERT INTO `password_resets` (id, user_id, token_hash, expires_at)
             VALUES (:id, :userId, :tokenHash, :expiresAt)',
            ['id' => $id, 'userId' => $userId, 'tokenHash' => $tokenHash, 'expiresAt' => $expiresAt]
        );
    }

    /**
     * Finds a valid (unexpired, unused) reset token by its hash.
     *
     * @return array<string, mixed>|null
     */
    public function findValidResetToken(string $tokenHash): ?array
    {
        $sql = 'SELECT pr.*, u.email, u.full_name
                FROM `password_resets` pr
                INNER JOIN `users` u ON u.id = pr.user_id
                WHERE pr.token_hash = :hash
                  AND pr.expires_at > NOW()
                  AND pr.used_at IS NULL
                LIMIT 1';

        $row = $this->execute($sql, ['hash' => $tokenHash])->fetch();
        return $row ?: null;
    }

    /**
     * Marks a password reset token as used.
     */
    public function consumeResetToken(string $resetId): void
    {
        $this->execute(
            'UPDATE `password_resets` SET used_at = NOW() WHERE id = :id',
            ['id' => $resetId]
        );
    }

    /**
     * Saves a hashed password to password_history.
     */
    public function addPasswordHistory(string $historyId, string $userId, string $hash): void
    {
        $this->execute(
            'INSERT INTO `password_history` (id, user_id, password_hash) VALUES (:id, :userId, :hash)',
            ['id' => $historyId, 'userId' => $userId, 'hash' => $hash]
        );
    }

    /**
     * Returns recent password hashes for history enforcement.
     *
     * @return string[]
     */
    public function getPasswordHistory(string $userId, int $count = 5): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT password_hash FROM `password_history`
             WHERE user_id = :userId
             ORDER BY created_at DESC
             LIMIT :count'
        );
        $stmt->bindValue(':userId', $userId);
        $stmt->bindValue(':count', $count, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
    }

    // ── Searchable Interface ───────────────────────────────────────────────────

    /**
     * @param  array<string, mixed> $criteria
     * @return list<array<string, mixed>>
     */
    public function search(array $criteria, int $limit = 20, int $offset = 0): array
    {
        [$sql, $params] = $this->buildSearchQuery($criteria);
        $sql .= ' ORDER BY u.full_name ASC LIMIT :limit OFFSET :offset';

        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue(":{$key}", $val);
        }
        $stmt->bindValue(':limit',  $limit,  PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function searchCount(array $criteria): int
    {
        [$sql, $params] = $this->buildSearchQuery($criteria, true);
        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue(":{$key}", $val);
        }
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    /**
     * @param  array<string, mixed> $criteria
     * @return array{string, array<string, mixed>}
     */
    private function buildSearchQuery(array $criteria, bool $countOnly = false): array
    {
        $select = $countOnly
            ? 'SELECT COUNT(*) FROM `users` u'
            : 'SELECT u.*, BIN_TO_UUID(u.id) AS uuid_str FROM `users` u';

        $where  = ['u.deleted_at IS NULL'];
        $params = [];

        if (!empty($criteria['q'])) {
            $where[]        = '(u.full_name LIKE :q OR u.email LIKE :q OR u.username LIKE :q)';
            $params['q']    = '%' . $criteria['q'] . '%';
        }

        if (!empty($criteria['status'])) {
            $where[]           = 'u.status = :status';
            $params['status']  = $criteria['status'];
        }

        if (!empty($criteria['agency_id'])) {
            $where[]              = 'u.agency_id = :agencyId';
            $params['agencyId']   = $criteria['agency_id'];
        }

        if (!empty($criteria['role_id'])) {
            $select .= $countOnly ? '' : '';
            $where[]            = 'EXISTS (SELECT 1 FROM user_roles ur WHERE ur.user_id = u.id AND ur.role_id = :roleId)';
            $params['roleId']   = $criteria['role_id'];
        }

        $sql = $select . ' WHERE ' . implode(' AND ', $where);
        return [$sql, $params];
    }
}
