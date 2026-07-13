-- ============================================================
-- Migration: 20260101_000003_create_users_table.sql
-- Central users table — single-table inheritance for all roles.
-- ============================================================

CREATE TABLE IF NOT EXISTS `users` (
    -- Identity
    `id`                    BINARY(16)      NOT NULL                COMMENT 'UUID stored as BINARY(16)',
    `uuid`                  VARCHAR(36)     NOT NULL                COMMENT 'Human-readable UUID for external references',

    -- Organization
    `agency_id`             BINARY(16)      NULL,
    `department_id`         BINARY(16)      NULL,

    -- Personal
    `full_name`             VARCHAR(200)    NOT NULL,
    `username`              VARCHAR(60)     NOT NULL,
    `email`                 VARCHAR(200)    NOT NULL,
    `phone`                 VARCHAR(100)    NULL                    COMMENT 'AES-256-GCM encrypted',
    `national_id`           VARCHAR(500)    NULL                    COMMENT 'AES-256-GCM encrypted',
    `profile_photo`         VARCHAR(500)    NULL,
    `gender`                ENUM('male','female','other')   NULL,
    `date_of_birth`         DATE            NULL,

    -- Location (for citizens)
    `region_id`             BINARY(16)      NULL,
    `district_id`           BINARY(16)      NULL,
    `ward_id`               BINARY(16)      NULL,
    `village_id`            BINARY(16)      NULL,
    `physical_address`      TEXT            NULL,

    -- Credentials
    `password_hash`         VARCHAR(255)    NOT NULL,
    `password_changed_at`   TIMESTAMP       NULL DEFAULT NULL,
    `password_expires_at`   TIMESTAMP       NULL DEFAULT NULL,

    -- Account Status
    `status`                ENUM('active','inactive','suspended','locked','pending_verification')
                                            NOT NULL DEFAULT 'pending_verification',
    `email_verified_at`     TIMESTAMP       NULL DEFAULT NULL,
    `phone_verified_at`     TIMESTAMP       NULL DEFAULT NULL,
    `email_verify_token`    VARCHAR(100)    NULL,
    `failed_login_attempts` TINYINT         NOT NULL DEFAULT 0,
    `locked_until`          TIMESTAMP       NULL DEFAULT NULL,

    -- Security
    `two_factor_enabled`    TINYINT(1)      NOT NULL DEFAULT 0,
    `two_factor_secret`     VARCHAR(255)    NULL,
    `remember_token`        VARCHAR(100)    NULL,

    -- Preferences
    `preferred_language`    VARCHAR(5)      NOT NULL DEFAULT 'en',
    `preferred_channel`     ENUM('email','sms','in_app') NOT NULL DEFAULT 'in_app',
    `timezone`              VARCHAR(50)     NOT NULL DEFAULT 'Africa/Dar_es_Salaam',

    -- Activity
    `last_login_at`         TIMESTAMP       NULL DEFAULT NULL,
    `last_login_ip`         VARCHAR(45)     NULL,

    -- Audit columns
    `created_at`            TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`            TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `created_by`            BINARY(16)      NULL,
    `updated_by`            BINARY(16)      NULL,
    `deleted_at`            TIMESTAMP       NULL DEFAULT NULL,
    `deleted_by`            BINARY(16)      NULL,

    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_users_uuid`     (`uuid`),
    UNIQUE KEY `idx_users_email`    (`email`),
    UNIQUE KEY `idx_users_username` (`username`),
    KEY `idx_users_status`          (`status`),
    KEY `idx_users_agency`          (`agency_id`),
    KEY `idx_users_department`      (`department_id`),
    KEY `idx_users_region`          (`region_id`),
    KEY `idx_users_deleted`         (`deleted_at`),

    CONSTRAINT `fk_users_agency`      FOREIGN KEY (`agency_id`)     REFERENCES `agencies`(`id`)     ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_users_department`  FOREIGN KEY (`department_id`) REFERENCES `departments`(`id`) ON DELETE SET NULL ON UPDATE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- User Roles Junction Table (many-to-many)
-- ============================================================

CREATE TABLE IF NOT EXISTS `user_roles` (
    `user_id`       BINARY(16)  NOT NULL,
    `role_id`       BINARY(16)  NOT NULL,
    `assigned_at`   TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `assigned_by`   BINARY(16)  NULL,
    PRIMARY KEY (`user_id`, `role_id`),
    KEY `idx_ur_role` (`role_id`),
    CONSTRAINT `fk_ur_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_ur_role` FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- Login History (append-only)
-- ============================================================

CREATE TABLE IF NOT EXISTS `login_history` (
    `id`            BINARY(16)      NOT NULL,
    `user_id`       BINARY(16)      NOT NULL,
    `ip_address`    VARCHAR(45)     NOT NULL,
    `user_agent`    VARCHAR(500)    NULL,
    `status`        ENUM('success','failed','locked')  NOT NULL,
    `failure_reason`VARCHAR(100)    NULL,
    `created_at`    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_lh_user`    (`user_id`),
    KEY `idx_lh_created` (`created_at`),
    CONSTRAINT `fk_lh_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- Password Resets
-- ============================================================

CREATE TABLE IF NOT EXISTS `password_resets` (
    `id`            BINARY(16)      NOT NULL,
    `user_id`       BINARY(16)      NOT NULL,
    `token_hash`    VARCHAR(255)    NOT NULL        COMMENT 'SHA-256 of the raw token',
    `expires_at`    TIMESTAMP       NOT NULL,
    `used_at`       TIMESTAMP       NULL DEFAULT NULL,
    `created_at`    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_pr_user`    (`user_id`),
    KEY `idx_pr_expires` (`expires_at`),
    CONSTRAINT `fk_pr_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- Password History (prevents reuse)
-- ============================================================

CREATE TABLE IF NOT EXISTS `password_history` (
    `id`            BINARY(16)      NOT NULL,
    `user_id`       BINARY(16)      NOT NULL,
    `password_hash` VARCHAR(255)    NOT NULL,
    `created_at`    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_ph_user` (`user_id`),
    CONSTRAINT `fk_ph_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
