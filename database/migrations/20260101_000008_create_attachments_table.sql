-- ============================================================
-- Migration: 20260101_000008_create_attachments_table.sql
-- Polymorphic table for incident and work order files.
-- ============================================================

CREATE TABLE IF NOT EXISTS `attachments` (
    `id`                BINARY(16)      NOT NULL,
    `entity_type`       ENUM('incident', 'work_order', 'update') NOT NULL COMMENT 'What this file is attached to',
    `entity_id`         BINARY(16)      NOT NULL COMMENT 'The ID of the incident/work_order/update',
    `uploader_id`       BINARY(16)      NOT NULL COMMENT 'Who uploaded the file',
    
    `original_name`     VARCHAR(255)    NOT NULL COMMENT 'Original filename from user',
    `stored_name`       VARCHAR(255)    NOT NULL COMMENT 'Secure randomized filename on disk',
    `file_path`         VARCHAR(500)    NOT NULL COMMENT 'Relative path in storage directory',
    
    `mime_type`         VARCHAR(100)    NOT NULL,
    `file_size`         INT UNSIGNED    NOT NULL COMMENT 'Size in bytes',
    `is_image`          TINYINT(1)      NOT NULL DEFAULT 0,
    
    `created_at`        TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `deleted_at`        TIMESTAMP       NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_att_entity` (`entity_type`, `entity_id`),
    KEY `idx_att_uploader` (`uploader_id`),
    CONSTRAINT `fk_att_uploader` FOREIGN KEY (`uploader_id`) REFERENCES `users`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
