-- ============================================================
-- Migration: 20260101_000006_create_workflow_logs_table.sql
-- Immutable audit log for incident state transitions.
-- ============================================================

CREATE TABLE IF NOT EXISTS `workflow_logs` (
    `id`                BINARY(16)      NOT NULL,
    `incident_id`       BINARY(16)      NOT NULL,
    `actor_id`          BINARY(16)      NOT NULL COMMENT 'User who performed the action',
    `action`            VARCHAR(50)     NOT NULL COMMENT 'e.g., verify, reject, assign, resolve',
    `from_status`       VARCHAR(50)     NOT NULL,
    `to_status`         VARCHAR(50)     NOT NULL,
    `comments`          TEXT            NULL COMMENT 'Reason or note provided by actor',
    `created_at`        TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_wl_incident` (`incident_id`),
    KEY `idx_wl_actor` (`actor_id`),
    KEY `idx_wl_created` (`created_at`),
    CONSTRAINT `fk_wl_incident` FOREIGN KEY (`incident_id`) REFERENCES `incidents`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_wl_actor` FOREIGN KEY (`actor_id`) REFERENCES `users`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
