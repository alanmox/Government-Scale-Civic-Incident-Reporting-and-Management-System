-- ============================================================
-- Migration: 20260101_000001_create_roles_table.sql
-- Roles must exist before users (FK dependency).
-- ============================================================

CREATE TABLE IF NOT EXISTS `roles` (
    `id`          BINARY(16)      NOT NULL,
    `name`        VARCHAR(80)     NOT NULL,
    `slug`        VARCHAR(80)     NOT NULL,
    `description` VARCHAR(255)    NULL,
    `is_system`   TINYINT(1)      NOT NULL DEFAULT 0 COMMENT '1 = cannot be deleted',
    `created_at`  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at`  TIMESTAMP       NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_roles_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- Migration: 20260101_000002_create_permissions_table.sql
-- ============================================================

CREATE TABLE IF NOT EXISTS `permissions` (
    `id`          BINARY(16)      NOT NULL,
    `name`        VARCHAR(120)    NOT NULL,
    `slug`        VARCHAR(120)    NOT NULL,
    `module`      VARCHAR(80)     NOT NULL COMMENT 'Grouping: incident, user, system, etc.',
    `description` VARCHAR(255)    NULL,
    `created_at`  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_permissions_slug` (`slug`),
    KEY `idx_permissions_module` (`module`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- Migration: 20260101_000003_create_role_permissions_table.sql
-- ============================================================

CREATE TABLE IF NOT EXISTS `role_permissions` (
    `role_id`       BINARY(16)  NOT NULL,
    `permission_id` BINARY(16)  NOT NULL,
    `granted_at`    TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `granted_by`    BINARY(16)  NULL,
    PRIMARY KEY (`role_id`, `permission_id`),
    CONSTRAINT `fk_rp_role`       FOREIGN KEY (`role_id`)       REFERENCES `roles`(`id`)       ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_rp_permission` FOREIGN KEY (`permission_id`) REFERENCES `permissions`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
