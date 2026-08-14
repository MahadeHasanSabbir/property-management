-- ---------------------------------------------------------------------------
-- Property Management — seed data for a fresh install.
--
-- Run after schema.sql:
--     mysql -u root property_v2 < database/seed.sql
--
-- SECURITY: the seeded administrator has must_change_password = 1, so the
-- temporary password below cannot survive first sign-in.
--
--     Sign in at:  /admin/login
--     E-mail:      admin@example.com
--     Password:    ChangeMe!2026     <-- you are forced to replace this
--
-- Change the e-mail to a real address you control, otherwise password reset
-- cannot reach you.
-- ---------------------------------------------------------------------------

SET NAMES utf8mb4;
USE `property_v2`;

-- ---------------------------------------------------------------------------
-- Plans.
--
-- These numbers are STARTING POINTS, not policy. Everything here is editable
-- at /admin/plans, so tuning the limits never requires a code change.
-- property_limit NULL = unlimited.
-- ---------------------------------------------------------------------------
INSERT INTO `plans`
    (`code`,  `name`,  `property_limit`, `can_upload_documents`, `can_export`, `is_default`, `sort_order`)
VALUES
    ('basic', 'Basic',              25,                      0,            0,            1,            1),
    ('pro',   'Pro',              NULL,                      1,            1,            0,            2)
ON DUPLICATE KEY UPDATE
    `name` = VALUES(`name`);

-- ---------------------------------------------------------------------------
-- The initial administrator.
--
-- Admins are ordinary rows in `users` distinguished by role, so more can be
-- created, promoted and demoted from the admin panel.
-- ---------------------------------------------------------------------------
INSERT INTO `users`
    (`user_code`, `name`, `email`, `phone`, `password`, `role`, `plan_code`,
     `status`, `locale`, `must_change_password`, `password_changed_at`)
VALUES
    (NULL,
     'Administrator',
     'admin@example.com',
     NULL,
     '$2y$10$X2sxEHrHbaR4OQxtlpfdZOVXbaYURIk62XGGF.f/plbbBQk1ovkra',
     'admin',
     NULL,
     'active',
     'en',
     1,
     NOW())
ON DUPLICATE KEY UPDATE
    `role` = 'admin';

-- ---------------------------------------------------------------------------
-- Settings. Key/value is appropriate here precisely because this set is
-- open-ended, unlike plan entitlements which are typed columns.
-- ---------------------------------------------------------------------------
INSERT INTO `settings` (`key_name`, `value`) VALUES
    ('site_name',          'Property Management'),
    ('contact_email',      'info@example.com'),
    ('contact_phone',      '+8801000000000'),
    ('contact_address',    'City, Upazila, District'),
    ('allow_registration', '1'),
    ('schema_version',     '1')
ON DUPLICATE KEY UPDATE
    `value` = VALUES(`value`);
