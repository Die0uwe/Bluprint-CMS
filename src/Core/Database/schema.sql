-- ============================================================
-- Community Fusion CMS — Database Schema v1.0
-- Engine: InnoDB | Charset: utf8mb4_unicode_ci
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- USERS
CREATE TABLE `cf_users` (
    `id`                INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `username`          VARCHAR(50) NOT NULL,
    `email`             VARCHAR(255) NOT NULL,
    `password_hash`     VARCHAR(255) NOT NULL,
    `display_name`      VARCHAR(100) NULL,
    `avatar_url`        VARCHAR(500) NULL,
    `bio`               TEXT NULL,
    `is_active`         TINYINT(1) NOT NULL DEFAULT 1,
    `is_verified`       TINYINT(1) NOT NULL DEFAULT 0,
    `email_verified_at` DATETIME NULL,
    `last_login_at`     DATETIME NULL,
    `last_login_ip`     VARCHAR(45) NULL,
    `locale`            VARCHAR(10) NOT NULL DEFAULT 'nl',
    `timezone`          VARCHAR(50) NOT NULL DEFAULT 'Europe/Amsterdam',
    `created_at`        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at`        DATETIME NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_username` (`username`),
    UNIQUE KEY `uq_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ROLES
CREATE TABLE `cf_roles` (
    `id`           SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`         VARCHAR(50) NOT NULL,
    `display_name` VARCHAR(100) NOT NULL,
    `description`  TEXT NULL,
    `color`        VARCHAR(7) NULL,
    `is_default`   TINYINT(1) NOT NULL DEFAULT 0,
    `priority`     SMALLINT NOT NULL DEFAULT 0,
    `created_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- PERMISSIONS
CREATE TABLE `cf_permissions` (
    `id`          SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`        VARCHAR(100) NOT NULL,
    `group`       VARCHAR(50) NOT NULL,
    `description` TEXT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ROLE <-> PERMISSIONS
CREATE TABLE `cf_role_permissions` (
    `role_id`       SMALLINT UNSIGNED NOT NULL,
    `permission_id` SMALLINT UNSIGNED NOT NULL,
    PRIMARY KEY (`role_id`, `permission_id`),
    CONSTRAINT `fk_rp_role`       FOREIGN KEY (`role_id`)       REFERENCES `cf_roles`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_rp_permission` FOREIGN KEY (`permission_id`) REFERENCES `cf_permissions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- USER <-> ROLES
CREATE TABLE `cf_user_roles` (
    `user_id`    INT UNSIGNED NOT NULL,
    `role_id`    SMALLINT UNSIGNED NOT NULL,
    `assigned_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `assigned_by` INT UNSIGNED NULL,
    PRIMARY KEY (`user_id`, `role_id`),
    CONSTRAINT `fk_ur_user` FOREIGN KEY (`user_id`) REFERENCES `cf_users`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_ur_role` FOREIGN KEY (`role_id`) REFERENCES `cf_roles`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- OAUTH
CREATE TABLE `cf_user_oauth` (
    `id`               INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`          INT UNSIGNED NOT NULL,
    `provider`         VARCHAR(50) NOT NULL,
    `provider_user_id` VARCHAR(255) NOT NULL,
    `access_token`     TEXT NOT NULL,
    `refresh_token`    TEXT NULL,
    `token_expires_at` DATETIME NULL,
    `scope`            TEXT NULL,
    `provider_data`    JSON NULL,
    `created_at`       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_provider_user` (`provider`, `provider_user_id`),
    CONSTRAINT `fk_oauth_user` FOREIGN KEY (`user_id`) REFERENCES `cf_users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- MODULES
CREATE TABLE `cf_modules` (
    `id`           SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `slug`         VARCHAR(100) NOT NULL,
    `name`         VARCHAR(150) NOT NULL,
    `version`      VARCHAR(20) NOT NULL,
    `author`       VARCHAR(100) NULL,
    `description`  TEXT NULL,
    `is_core`      TINYINT(1) NOT NULL DEFAULT 0,
    `is_enabled`   TINYINT(1) NOT NULL DEFAULT 1,
    `config`       JSON NULL,
    `installed_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- BLOCK TYPES
CREATE TABLE `cf_block_types` (
    `id`          SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `module_id`   SMALLINT UNSIGNED NOT NULL,
    `slug`        VARCHAR(100) NOT NULL,
    `name`        VARCHAR(150) NOT NULL,
    `description` TEXT NULL,
    `icon`        VARCHAR(100) NULL,
    `schema`      JSON NOT NULL,
    `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_slug` (`slug`),
    CONSTRAINT `fk_bt_module` FOREIGN KEY (`module_id`) REFERENCES `cf_modules`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- BLOCK INSTANCES
CREATE TABLE `cf_blocks` (
    `id`               INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `block_type_id`    SMALLINT UNSIGNED NOT NULL,
    `zone`             VARCHAR(50) NOT NULL,
    `position`         SMALLINT NOT NULL DEFAULT 0,
    `title`            VARCHAR(200) NULL,
    `config`           JSON NULL,
    `is_visible`       TINYINT(1) NOT NULL DEFAULT 1,
    `visibility_roles` JSON NULL,
    `cache_ttl`        SMALLINT UNSIGNED NULL,
    `created_at`       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_zone_position` (`zone`, `position`),
    CONSTRAINT `fk_block_type` FOREIGN KEY (`block_type_id`) REFERENCES `cf_block_types`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- SETTINGS
CREATE TABLE `cf_settings` (
    `id`          SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `group`       VARCHAR(50) NOT NULL,
    `key`         VARCHAR(100) NOT NULL,
    `value`       TEXT NULL,
    `type`        ENUM('string','int','bool','json','encrypted') NOT NULL DEFAULT 'string',
    `label`       VARCHAR(200) NULL,
    `description` TEXT NULL,
    `is_public`   TINYINT(1) NOT NULL DEFAULT 0,
    `updated_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_group_key` (`group`, `key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- CATEGORIES
CREATE TABLE `cf_categories` (
    `id`          SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `parent_id`   SMALLINT UNSIGNED NULL,
    `type`        VARCHAR(50) NOT NULL,
    `slug`        VARCHAR(150) NOT NULL,
    `name`        VARCHAR(200) NOT NULL,
    `description` TEXT NULL,
    `position`    SMALLINT NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_type_slug` (`type`, `slug`),
    CONSTRAINT `fk_cat_parent` FOREIGN KEY (`parent_id`) REFERENCES `cf_categories`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- NEWS
CREATE TABLE `cf_news` (
    `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `author_id`      INT UNSIGNED NOT NULL,
    `category_id`    SMALLINT UNSIGNED NULL,
    `slug`           VARCHAR(200) NOT NULL,
    `title`          VARCHAR(300) NOT NULL,
    `summary`        TEXT NULL,
    `content`        LONGTEXT NOT NULL,
    `featured_image` VARCHAR(500) NULL,
    `status`         ENUM('draft','published','archived') NOT NULL DEFAULT 'draft',
    `is_sticky`      TINYINT(1) NOT NULL DEFAULT 0,
    `views`          INT UNSIGNED NOT NULL DEFAULT 0,
    `comment_count`  INT UNSIGNED NOT NULL DEFAULT 0,
    `published_at`   DATETIME NULL,
    `created_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at`     DATETIME NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_slug` (`slug`),
    KEY `idx_status_published` (`status`, `published_at`),
    CONSTRAINT `fk_news_author`   FOREIGN KEY (`author_id`)   REFERENCES `cf_users`(`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_news_category` FOREIGN KEY (`category_id`) REFERENCES `cf_categories`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- PAGES
CREATE TABLE `cf_pages` (
    `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `author_id`     INT UNSIGNED NOT NULL,
    `parent_id`     INT UNSIGNED NULL,
    `slug`          VARCHAR(200) NOT NULL,
    `title`         VARCHAR(300) NOT NULL,
    `content`       LONGTEXT NOT NULL,
    `template`      VARCHAR(100) NOT NULL DEFAULT 'default',
    `meta_title`    VARCHAR(200) NULL,
    `meta_desc`     VARCHAR(400) NULL,
    `status`        ENUM('draft','published') NOT NULL DEFAULT 'draft',
    `menu_position` SMALLINT NULL,
    `created_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at`    DATETIME NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_slug` (`slug`),
    CONSTRAINT `fk_page_author` FOREIGN KEY (`author_id`) REFERENCES `cf_users`(`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_page_parent` FOREIGN KEY (`parent_id`) REFERENCES `cf_pages`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- DEFAULT SEED DATA
INSERT INTO `cf_roles` (`name`, `display_name`, `priority`, `is_default`) VALUES
('super_admin', 'Super Admin', 100, 0),
('admin',       'Admin',       80,  0),
('moderator',   'Moderator',   50,  0),
('member',      'Member',      10,  1),
('guest',       'Gast',        0,   0);

INSERT INTO `cf_modules` (`slug`, `name`, `version`, `is_core`, `is_enabled`) VALUES
('users',    'Gebruikersbeheer', '1.0.0', 1, 1),
('news',     'Nieuws',           '1.0.0', 1, 1),
('pages',    'Pagina\'s',        '1.0.0', 1, 1),
('settings', 'Instellingen',     '1.0.0', 1, 1);

SET FOREIGN_KEY_CHECKS = 1;
