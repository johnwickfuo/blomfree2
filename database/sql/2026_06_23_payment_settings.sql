-- =====================================================================
-- BLOMFREE — Admin-managed payment gateway credentials
-- Generated: 2026-06-23
--
-- Adds the six `settings` rows that back the new "Payments" tab on the
-- admin Site Settings page (Paystack + Flutterwave keys). The
-- secret/hash/encryption rows are written by the admin UI as Laravel
-- encrypted strings; this SQL just seeds the keys as empty so the rows
-- exist on day one. Idempotent — safe to re-run.
--
-- No schema changes.
-- =====================================================================

INSERT INTO `settings` (`key`, `value`, `created_at`, `updated_at`)
VALUES
    ('paystack_public_key',          '', NOW(), NOW()),
    ('paystack_secret_key',          '', NOW(), NOW()),
    ('flutterwave_public_key',       '', NOW(), NOW()),
    ('flutterwave_secret_key',       '', NOW(), NOW()),
    ('flutterwave_encryption_key',   '', NOW(), NOW()),
    ('flutterwave_secret_hash',      '', NOW(), NOW())
ON DUPLICATE KEY UPDATE `updated_at` = `updated_at`;
-- The no-op ON DUPLICATE KEY clause preserves any existing values
-- (e.g. credentials an admin has already entered).
