-- =====================================================================
-- BLOMFREE — Branding settings + Real Estate rename
-- Generated: 2026-06-14
--
-- Run this against the production database to apply the DB-side changes
-- that ship with the Real Estate rename + admin-uploadable site logo and
-- CEO image. Safe to re-run (all statements are idempotent).
--
-- No schema changes — only two new rows in the existing `settings` table.
-- Image files themselves are stored on the public disk (storage/app/public)
-- and resolved through the URL-rewriting `/storage` symlink. Make sure
-- `php artisan storage:link` has been run on the server once.
-- =====================================================================

-- ---------------------------------------------------------------------
-- 1. Seed the two branding setting keys, empty until an admin uploads.
-- ---------------------------------------------------------------------
INSERT INTO `settings` (`key`, `value`, `created_at`, `updated_at`)
VALUES
    ('site_logo_path',  '', NOW(), NOW()),
    ('ceo_image_path',  '', NOW(), NOW())
ON DUPLICATE KEY UPDATE `updated_at` = `updated_at`;
-- Note: ON DUPLICATE KEY ... = updated_at is a no-op that preserves any
-- existing value (e.g. a logo an admin has already uploaded). This makes
-- the migration safe to run more than once.


-- ---------------------------------------------------------------------
-- 2. (Optional) Rename the customer-visible "Lands" subsidiary label
--    inside the `installment_plans.subsidiary` enum.
--
-- The current enum stores the value 'lands'. The rename in this release
-- is UI-only and the internal value is intentionally kept as 'lands' to
-- avoid breaking existing rows, route names and code paths.
--
-- If, later, you want to migrate the stored value too, uncomment the
-- block below. It (a) widens the enum to include both values, (b) copies
-- existing rows, then (c) narrows it back. Test on a copy first.
-- ---------------------------------------------------------------------
-- ALTER TABLE `installment_plans`
--     MODIFY COLUMN `subsidiary` ENUM('lands','real_estate','gadgets') NOT NULL;
--
-- UPDATE `installment_plans` SET `subsidiary` = 'real_estate' WHERE `subsidiary` = 'lands';
--
-- ALTER TABLE `installment_plans`
--     MODIFY COLUMN `subsidiary` ENUM('real_estate','gadgets') NOT NULL;
