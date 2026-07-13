-- ============================================================
-- Migration: 20260101_000007_create_work_orders_table.sql
-- Work Orders and Progress Updates tracking.
-- ============================================================

CREATE TABLE IF NOT EXISTS `work_orders` (
    `id`                BINARY(16)      NOT NULL,
    `incident_id`       BINARY(16)      NOT NULL,
    `officer_id`        BINARY(16)      NOT NULL COMMENT 'The assigned officer handling the work order',
    `reference_number`  VARCHAR(30)     NOT NULL COMMENT 'Format: WO-INC-2026-0001',
    `title`             VARCHAR(200)    NOT NULL,
    `description`       TEXT            NOT NULL,
    `status`            ENUM('pending','in_progress','on_hold','completed','cancelled') NOT NULL DEFAULT 'pending',
    `priority`          ENUM('low','medium','high','critical') NOT NULL DEFAULT 'medium',
    `estimated_cost`    DECIMAL(12, 2)  NULL,
    `actual_cost`       DECIMAL(12, 2)  NULL,
    `started_at`        TIMESTAMP       NULL DEFAULT NULL,
    `completed_at`      TIMESTAMP       NULL DEFAULT NULL,
    `created_at`        TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at`        TIMESTAMP       NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_wo_ref` (`reference_number`),
    KEY `idx_wo_incident` (`incident_id`),
    KEY `idx_wo_officer` (`officer_id`),
    KEY `idx_wo_status` (`status`),
    CONSTRAINT `fk_wo_incident` FOREIGN KEY (`incident_id`) REFERENCES `incidents`(`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_wo_officer` FOREIGN KEY (`officer_id`) REFERENCES `users`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Work Order Updates (Progress Tracking)
-- ============================================================

CREATE TABLE IF NOT EXISTS `work_order_updates` (
    `id`                BINARY(16)      NOT NULL,
    `work_order_id`     BINARY(16)      NOT NULL,
    `officer_id`        BINARY(16)      NOT NULL,
    `progress_percent`  TINYINT         NOT NULL DEFAULT 0,
    `notes`             TEXT            NOT NULL,
    `is_internal`       TINYINT(1)      NOT NULL DEFAULT 1 COMMENT '1=Only visible to officers, 0=Visible to citizen',
    `created_at`        TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_wou_work_order` (`work_order_id`),
    CONSTRAINT `fk_wou_work_order` FOREIGN KEY (`work_order_id`) REFERENCES `work_orders`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_wou_officer` FOREIGN KEY (`officer_id`) REFERENCES `users`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
