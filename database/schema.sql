-- ---------------------------------------------------------------------------
-- Property Management — schema.
--
-- Conventions:
--   - utf8mb4_unicode_ci throughout (NOT utf8mb4_0900_ai_ci, which does not
--     exist on MariaDB).
--   - AUTO_INCREMENT surrogate keys everywhere, so concurrent inserts cannot
--     collide on a natural key.
--   - Real foreign keys, with ON DELETE behaviour stated explicitly.
--   - IPs as VARCHAR(45), which is wide enough for IPv6.
--
-- Usage:  mysql -u root < database/schema.sql
-- ---------------------------------------------------------------------------

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS `property_v2`
    DEFAULT CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `property_v2`;

DROP TABLE IF EXISTS `audit_logs`;
DROP TABLE IF EXISTS `password_resets`;
DROP TABLE IF EXISTS `login_attempts`;
DROP TABLE IF EXISTS `page_views`;
DROP TABLE IF EXISTS `contact_messages`;
DROP TABLE IF EXISTS `property_documents`;
DROP TABLE IF EXISTS `property_identifiers`;
DROP TABLE IF EXISTS `properties`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `plans`;
DROP TABLE IF EXISTS `settings`;

-- ---------------------------------------------------------------------------
-- plans — entitlements, editable from the admin panel.
--
-- Typed columns rather than a key/value feature table: there are two plans and
-- three entitlements of mixed type, and an admin form has to render them. A k/v
-- table would force every value through a string and forfeit NOT NULL.
-- ---------------------------------------------------------------------------
CREATE TABLE `plans` (
    `code`                 VARCHAR(20)  NOT NULL,
    `name`                 VARCHAR(40)  NOT NULL,
    -- NULL means unlimited. Deliberately nullable rather than a sentinel like
    -- -1, so "no limit" is expressible in the type system.
    `property_limit`       INT UNSIGNED NULL DEFAULT NULL,
    `can_upload_documents` TINYINT(1)   NOT NULL DEFAULT 0,
    `can_export`           TINYINT(1)   NOT NULL DEFAULT 0,
    `is_default`           TINYINT(1)   NOT NULL DEFAULT 0,
    `sort_order`           TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `created_at`           DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`           DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`code`),
    KEY `idx_plans_default` (`is_default`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- users — one table for every account, whatever its role.
--
-- A single table rather than one per role: audit_logs.user_id needs a single
-- foreign-key target, and one identity means one session key and one guard.
--
-- Login is by email. `user_code` is a human-facing reference shown in the UI;
-- it is not a credential.
-- ---------------------------------------------------------------------------
CREATE TABLE `users` (
    `id`                      BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_code`               VARCHAR(20)  NULL DEFAULT NULL,
    `name`                    VARCHAR(100) NOT NULL,
    `email`                   VARCHAR(190) NOT NULL,
    `phone`                   VARCHAR(20)  NULL DEFAULT NULL,
    -- 255 leaves room beyond bcrypt's 60 characters for a future algorithm.
    `password`                VARCHAR(255) NOT NULL,
    `role`                    ENUM('customer','staff','admin') NOT NULL DEFAULT 'customer',
    `plan_code`               VARCHAR(20)  NULL DEFAULT NULL,
    -- Per-user escape hatch so one account can exceed its plan without
    -- inventing a new plan.
    `property_limit_override` INT UNSIGNED NULL DEFAULT NULL,
    -- Account lifecycle. Not a presence flag — it is never toggled by
    -- signing in or out.
    `status`                  ENUM('pending','active','suspended') NOT NULL DEFAULT 'active',
    `locale`                  VARCHAR(5)   NOT NULL DEFAULT 'en',
    `must_change_password`    TINYINT(1)   NOT NULL DEFAULT 0,
    `email_verified_at`       DATETIME     NULL DEFAULT NULL,
    `password_changed_at`     DATETIME     NULL DEFAULT NULL,
    `last_login_at`           DATETIME     NULL DEFAULT NULL,
    `last_login_ip`           VARCHAR(45)  NULL DEFAULT NULL,
    `created_at`              DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`              DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at`              DATETIME     NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_users_email` (`email`),
    UNIQUE KEY `uniq_users_code` (`user_code`),
    KEY `idx_users_role` (`role`),
    KEY `idx_users_status` (`status`),
    KEY `idx_users_plan` (`plan_code`),
    KEY `idx_users_created` (`created_at`),
    CONSTRAINT `fk_users_plan` FOREIGN KEY (`plan_code`)
        REFERENCES `plans` (`code`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- properties — one row per land-deed record.
--
-- The four dag/khatian columns keep the raw string EXACTLY as the user typed
-- it, commas and all. That string is canonical; property_identifiers below is a
-- derived index rebuilt from it. A bug in the comma splitter can therefore
-- never lose data.
-- ---------------------------------------------------------------------------
CREATE TABLE `properties` (
    `id`               BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`          BIGINT UNSIGNED NOT NULL,
    -- Per-user display number, shown as the "NO" column.
    `seq`              INT UNSIGNED  NOT NULL,
    `deed_no`          VARCHAR(40)   NOT NULL DEFAULT '',
    -- Nullable: the registration date is frequently unknown, and a NOT NULL
    -- date column would force a meaningless zero date under strict mode.
    `deed_date`        DATE          NULL DEFAULT NULL,
    -- Numeric, so range search and totals are possible.
    `area_cent`        DECIMAL(12,3) NULL DEFAULT NULL,
    `old_owner`        VARCHAR(120)  NOT NULL DEFAULT '',
    `new_owner`        VARCHAR(120)  NOT NULL DEFAULT '',
    `mouja`            VARCHAR(100)  NOT NULL DEFAULT '',
    `dag_current`      VARCHAR(255)  NOT NULL DEFAULT '',
    `dag_previous`     VARCHAR(255)  NOT NULL DEFAULT '',
    `khatian_current`  VARCHAR(255)  NOT NULL DEFAULT '',
    `khatian_previous` VARCHAR(255)  NOT NULL DEFAULT '',
    `notes`            TEXT          NULL DEFAULT NULL,
    `created_at`       DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`       DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at`       DATETIME      NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_properties_user_seq` (`user_id`, `seq`),
    -- NOTE: deliberately NO unique key on (user_id, deed_no). One deed can
    -- cover several plots, so duplicate deed numbers are legitimate.
    KEY `idx_properties_user`  (`user_id`, `deleted_at`),
    KEY `idx_properties_mouja` (`user_id`, `mouja`),
    KEY `idx_properties_deed`  (`user_id`, `deed_no`),
    KEY `idx_properties_date`  (`user_id`, `deed_date`),
    KEY `idx_properties_area`  (`user_id`, `area_cent`),
    KEY `idx_properties_old_owner` (`user_id`, `old_owner`),
    KEY `idx_properties_new_owner` (`user_id`, `new_owner`),
    CONSTRAINT `fk_properties_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- property_identifiers — the comma lists, split into rows.
--
-- Searching a comma list with LIKE '%12,%' cannot use an index, and is wrong
-- besides: it also matches the stored values '512,34' and '3412'. Splitting the
-- list into rows makes it an exact, indexed lookup.
--
-- FULLTEXT was not an option: innodb_ft_min_token_size is 3 on this server and
-- real dag values include '2', '5', '11' and '12', which would never be
-- indexed. Multi-value indexes over JSON need MySQL 8.0.17+; this is MariaDB
-- 10.4. On this server a child table is the only index-friendly design.
--
-- Rebuildable at any time with:  php database/reindex.php
-- ---------------------------------------------------------------------------
CREATE TABLE `property_identifiers` (
    `property_id` BIGINT UNSIGNED NOT NULL,
    -- Denormalised so a search never has to join back to properties or users.
    `user_id`     BIGINT UNSIGNED NOT NULL,
    `kind`        ENUM('dag','khatian')      NOT NULL,
    `scope`       ENUM('current','previous') NOT NULL,
    `value`       VARCHAR(30) NOT NULL,
    -- Composite PK de-duplicates a list like "12,12,12" for free.
    PRIMARY KEY (`property_id`, `kind`, `scope`, `value`),
    -- Serves both the scoped and the unscoped (current-or-previous) lookup.
    KEY `idx_ident_lookup` (`user_id`, `kind`, `value`, `scope`),
    CONSTRAINT `fk_ident_property` FOREIGN KEY (`property_id`)
        REFERENCES `properties` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- property_documents — scanned deeds and khatian papers.
--
-- Files live on disk under storage/uploads/, never in the database:
-- max_allowed_packet is 1 MB on this server, so BLOBs would fail immediately.
-- ---------------------------------------------------------------------------
CREATE TABLE `property_documents` (
    `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `property_id`   BIGINT UNSIGNED NOT NULL,
    -- Denormalised so the download controller can authorise without a join.
    `user_id`       BIGINT UNSIGNED NOT NULL,
    `original_name` VARCHAR(255) NOT NULL,
    -- Random name with a .bin extension on disk, so a smuggled .php cannot
    -- execute even if the storage deny rule is ever removed.
    `stored_name`   VARCHAR(80)  NOT NULL,
    `mime`          VARCHAR(100) NOT NULL,
    `size_bytes`    INT UNSIGNED NOT NULL,
    `sha256`        CHAR(64)     NOT NULL,
    `created_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_documents_stored` (`stored_name`),
    KEY `idx_documents_property` (`property_id`),
    KEY `idx_documents_user` (`user_id`),
    CONSTRAINT `fk_documents_property` FOREIGN KEY (`property_id`)
        REFERENCES `properties` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- contact_messages — submissions from the public contact form.
-- ---------------------------------------------------------------------------
CREATE TABLE `contact_messages` (
    `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`       VARCHAR(100) NOT NULL,
    `email`      VARCHAR(190) NOT NULL,
    `message`    TEXT         NOT NULL,
    `ip`         VARCHAR(45)  NULL DEFAULT NULL,
    `user_agent` VARCHAR(255) NULL DEFAULT NULL,
    `status`     ENUM('new','read') NOT NULL DEFAULT 'new',
    `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `read_at`    DATETIME     NULL DEFAULT NULL,
    `read_by`    BIGINT UNSIGNED NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_messages_status` (`status`, `created_at`),
    CONSTRAINT `fk_messages_reader` FOREIGN KEY (`read_by`)
        REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- page_views — replaces `visitors`.
--
-- An AUTO_INCREMENT key, not a timestamp: two visits in the same second must
-- both be recorded.
-- ---------------------------------------------------------------------------
CREATE TABLE `page_views` (
    `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `ip`         VARCHAR(45)  NULL DEFAULT NULL,
    `path`       VARCHAR(190) NOT NULL DEFAULT '',
    `user_id`    BIGINT UNSIGNED NULL DEFAULT NULL,
    `user_agent` VARCHAR(255) NULL DEFAULT NULL,
    `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_views_created` (`created_at`),
    KEY `idx_views_ip` (`ip`, `created_at`),
    CONSTRAINT `fk_views_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- audit_logs — who changed what, and when.
-- ---------------------------------------------------------------------------
CREATE TABLE `audit_logs` (
    `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`     BIGINT UNSIGNED NULL DEFAULT NULL,
    -- Snapshot of the actor's name/email at the time, so the log stays readable
    -- after the account is deleted and user_id goes NULL.
    `actor_label` VARCHAR(190) NULL DEFAULT NULL,
    `action`      VARCHAR(64)  NOT NULL,
    `entity`      VARCHAR(64)  NULL DEFAULT NULL,
    `entity_id`   VARCHAR(64)  NULL DEFAULT NULL,
    `meta`        TEXT         NULL DEFAULT NULL,
    `ip`          VARCHAR(45)  NULL DEFAULT NULL,
    `created_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_audit_created` (`created_at`),
    KEY `idx_audit_user` (`user_id`, `created_at`),
    KEY `idx_audit_entity` (`entity`, `entity_id`),
    CONSTRAINT `fk_audit_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- login_attempts — brute-force throttling.
--
-- Narrow and indexed on (identifier, created_at): on XAMPP every request comes
-- from 127.0.0.1, so identifier-first throttling is the only one that means
-- anything locally.
-- ---------------------------------------------------------------------------
CREATE TABLE `login_attempts` (
    `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `identifier` VARCHAR(190) NOT NULL,
    `ip`         VARCHAR(45)  NULL DEFAULT NULL,
    `successful` TINYINT(1)   NOT NULL DEFAULT 0,
    `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_attempts_identifier` (`identifier`, `created_at`),
    KEY `idx_attempts_ip` (`ip`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- password_resets.
--
-- Only the SHA-256 of the token is stored, so a database read does not hand
-- over working reset links.
-- ---------------------------------------------------------------------------
CREATE TABLE `password_resets` (
    `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`    BIGINT UNSIGNED NOT NULL,
    `token_hash` CHAR(64)  NOT NULL,
    `expires_at` DATETIME  NOT NULL,
    `used_at`    DATETIME  NULL DEFAULT NULL,
    `created_at` DATETIME  NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_resets_token` (`token_hash`),
    KEY `idx_resets_user` (`user_id`),
    CONSTRAINT `fk_resets_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- settings — the one genuinely open-ended thing, so the one key/value table.
-- ---------------------------------------------------------------------------
CREATE TABLE `settings` (
    `key_name`   VARCHAR(64) NOT NULL,
    `value`      TEXT        NULL DEFAULT NULL,
    `updated_at` DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`key_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
