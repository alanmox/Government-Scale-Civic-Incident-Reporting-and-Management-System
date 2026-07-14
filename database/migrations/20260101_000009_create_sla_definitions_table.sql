-- 20260101_000009_create_sla_definitions_table.sql

CREATE TABLE IF NOT EXISTS `sla_definitions` (
    `id` BINARY(16) NOT NULL,
    `category_id` BINARY(16) NOT NULL,
    `priority` VARCHAR(20) NOT NULL COMMENT 'critical, high, medium, low',
    `resolve_hours` INT NOT NULL DEFAULT 72 COMMENT 'Target time to resolve',
    `escalate_hours` INT NOT NULL DEFAULT 48 COMMENT 'Time before auto-escalation warning',
    
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `created_by` BINARY(16) NOT NULL,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `updated_by` BINARY(16) NOT NULL,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,
    `deleted_by` BINARY(16) NULL,
    
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_sla_category_priority` (`category_id`, `priority`),
    CONSTRAINT `fk_sla_category` FOREIGN KEY (`category_id`) REFERENCES `incident_categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
