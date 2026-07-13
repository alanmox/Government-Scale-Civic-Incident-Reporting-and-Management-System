-- ============================================================
-- Migration: 20260101_000004_create_categories_table.sql
-- Defines incident categories and SLA limits.
-- ============================================================

CREATE TABLE IF NOT EXISTS `incident_categories` (
    `id`                BINARY(16)      NOT NULL,
    `name`              VARCHAR(150)    NOT NULL,
    `slug`              VARCHAR(150)    NOT NULL,
    `description`       TEXT            NULL,
    `default_priority`  ENUM('low','medium','high','critical','emergency') NOT NULL DEFAULT 'medium',
    `sla_hours`         INT UNSIGNED    NOT NULL DEFAULT 72 COMMENT 'Service Level Agreement resolution time limit',
    `agency_id`         BINARY(16)      NULL COMMENT 'Default routing agency for this category',
    `is_active`         TINYINT(1)      NOT NULL DEFAULT 1,
    `created_at`        TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `created_by`        BINARY(16)      NULL,
    `updated_by`        BINARY(16)      NULL,
    `deleted_at`        TIMESTAMP       NULL DEFAULT NULL,
    `deleted_by`        BINARY(16)      NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_categories_slug` (`slug`),
    KEY `idx_categories_active` (`is_active`),
    CONSTRAINT `fk_categories_agency` FOREIGN KEY (`agency_id`) REFERENCES `agencies`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
