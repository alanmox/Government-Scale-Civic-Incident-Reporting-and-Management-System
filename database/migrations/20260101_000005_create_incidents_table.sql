-- ============================================================
-- Migration: 20260101_000005_create_incidents_table.sql
-- Core incidents tracking table.
-- ============================================================

CREATE TABLE IF NOT EXISTS `incidents` (
    `id`                BINARY(16)      NOT NULL,
    `reference_number`  VARCHAR(20)     NOT NULL COMMENT 'Human-readable ID like INC-2026-0001',
    `citizen_id`        BINARY(16)      NOT NULL COMMENT 'The user who reported the incident',
    
    -- Categorization & Priority
    `category_id`       BINARY(16)      NOT NULL,
    `title`             VARCHAR(200)    NOT NULL,
    `description`       TEXT            NOT NULL,
    `priority`          ENUM('low','medium','high','critical','emergency') NOT NULL DEFAULT 'medium',
    
    -- State Machine
    `status`            ENUM('draft','submitted','received','verified','rejected','assigned','in_progress','resolved','closed','archived') NOT NULL DEFAULT 'submitted',
    `sub_status`        VARCHAR(100)    NULL COMMENT 'Additional state info (e.g., waiting_for_citizen)',
    
    -- Location Data
    `region_id`         BINARY(16)      NULL,
    `district_id`       BINARY(16)      NULL,
    `ward_id`           BINARY(16)      NULL,
    `village_id`        BINARY(16)      NULL,
    `latitude`          DECIMAL(10, 8)  NULL,
    `longitude`         DECIMAL(11, 8)  NULL,
    `location_desc`     VARCHAR(500)    NULL,
    
    -- SLA Tracking
    `reported_at`       TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `sla_due_at`        TIMESTAMP       NULL DEFAULT NULL,
    `resolved_at`       TIMESTAMP       NULL DEFAULT NULL,
    
    -- Routing & Assignment
    `agency_id`         BINARY(16)      NULL,
    `department_id`     BINARY(16)      NULL,
    `assigned_officer_id` BINARY(16)    NULL,
    
    -- Metadata / Audit
    `is_public`         TINYINT(1)      NOT NULL DEFAULT 0,
    `created_at`        TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at`        TIMESTAMP       NULL DEFAULT NULL,
    `deleted_by`        BINARY(16)      NULL,
    
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_incidents_ref` (`reference_number`),
    KEY `idx_incidents_citizen` (`citizen_id`),
    KEY `idx_incidents_category` (`category_id`),
    KEY `idx_incidents_status` (`status`),
    KEY `idx_incidents_priority` (`priority`),
    KEY `idx_incidents_agency` (`agency_id`),
    KEY `idx_incidents_assigned` (`assigned_officer_id`),
    KEY `idx_incidents_reported` (`reported_at`),
    
    CONSTRAINT `fk_incidents_citizen` FOREIGN KEY (`citizen_id`) REFERENCES `users`(`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_incidents_category` FOREIGN KEY (`category_id`) REFERENCES `incident_categories`(`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_incidents_agency` FOREIGN KEY (`agency_id`) REFERENCES `agencies`(`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_incidents_department` FOREIGN KEY (`department_id`) REFERENCES `departments`(`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_incidents_officer` FOREIGN KEY (`assigned_officer_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
