-- ============================================================
-- Migration: 20260101_000000_create_migrations_table.sql
-- Tracks which migrations have been executed.
-- ============================================================

CREATE TABLE IF NOT EXISTS `migrations` (
    `id`         INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `migration`  VARCHAR(255)    NOT NULL,
    `batch`      INT UNSIGNED    NOT NULL DEFAULT 1,
    `executed_at`TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_migrations_name` (`migration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
