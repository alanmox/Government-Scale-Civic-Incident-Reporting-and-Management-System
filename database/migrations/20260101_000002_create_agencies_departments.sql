-- ============================================================
-- Migration: 20260101_000002_create_agencies_departments.sql
-- Agencies and departments before users (FK dependency).
-- ============================================================

CREATE TABLE IF NOT EXISTS `agencies` (
    `id`            BINARY(16)      NOT NULL,
    `name`          VARCHAR(200)    NOT NULL,
    `code`          VARCHAR(30)     NOT NULL,
    `acronym`       VARCHAR(20)     NULL,
    `description`   TEXT            NULL,
    `phone`         VARCHAR(20)     NULL,
    `email`         VARCHAR(150)    NULL,
    `address`       VARCHAR(255)    NULL,
    `logo_path`     VARCHAR(500)    NULL,
    `is_active`     TINYINT(1)      NOT NULL DEFAULT 1,
    `created_at`    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `created_by`    BINARY(16)      NULL,
    `deleted_at`    TIMESTAMP       NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_agencies_code` (`code`),
    KEY `idx_agencies_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `departments` (
    `id`            BINARY(16)      NOT NULL,
    `agency_id`     BINARY(16)      NOT NULL,
    `name`          VARCHAR(200)    NOT NULL,
    `code`          VARCHAR(30)     NOT NULL,
    `description`   TEXT            NULL,
    `head_user_id`  BINARY(16)      NULL,
    `is_active`     TINYINT(1)      NOT NULL DEFAULT 1,
    `created_at`    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `created_by`    BINARY(16)      NULL,
    `deleted_at`    TIMESTAMP       NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_dept_agency_code` (`agency_id`, `code`),
    KEY `idx_dept_agency` (`agency_id`),
    CONSTRAINT `fk_dept_agency` FOREIGN KEY (`agency_id`) REFERENCES `agencies`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
