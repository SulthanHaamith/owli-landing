-- ============================================================
-- Arokya Payanam 2026 — registration database
-- Import ONCE through hPanel -> Databases -> phpMyAdmin -> Import
-- (select your database first). Safe to re-run: uses IF NOT EXISTS
-- and INSERT IGNORE, so it never overwrites existing data.
--
-- All money is stored in PAISE (Rs 299 = 29900).
-- ============================================================

SET NAMES utf8mb4;

-- Small key/value store (caches the Coaxlure API token for ~25 days)
CREATE TABLE IF NOT EXISTS settings (
  skey       VARCHAR(50)  NOT NULL PRIMARY KEY,
  sval       TEXT         NOT NULL,
  updated_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Price tiers. A row with valid_from / valid_until set (early-bird, late fee)
-- wins over an open-ended row for the same category while its window is active.
CREATE TABLE IF NOT EXISTS tiers (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  category      ENUM('student','doctor','bni','general') NOT NULL,
  label         VARCHAR(60)  NOT NULL DEFAULT 'Standard',
  amount_paise  INT UNSIGNED NOT NULL,
  valid_from    DATETIME NULL,
  valid_until   DATETIME NULL,
  active        TINYINT(1)   NOT NULL DEFAULT 1,
  KEY idx_tier_cat (category, active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Coupon codes.
--   discount_type 'percent'     -> value = 1..100          (percent off)
--   discount_type 'flat'        -> value = paise off       (e.g. 10000 = Rs 100 off)
--   discount_type 'fixed_price' -> value = paise to pay    (e.g. 19900 = pay Rs 199)
--   category NULL = works for every category
--   max_uses NULL = unlimited (counted on PAID registrations only)
CREATE TABLE IF NOT EXISTS coupons (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  code          VARCHAR(30)  NOT NULL UNIQUE,
  discount_type ENUM('percent','flat','fixed_price') NOT NULL,
  value         INT UNSIGNED NOT NULL,
  category      ENUM('student','doctor','bni','general') NULL,
  max_uses      INT UNSIGNED NULL,
  valid_from    DATETIME NULL,
  valid_until   DATETIME NULL,
  active        TINYINT(1)   NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- One row per person registering.
-- source: 'web'   = the attendee used the public form (audit trail — the admin
--                  console refuses to delete these rows, ever, by design)
--         'staff' = added by an organiser in the admin console ("Add entry",
--                  recorded as paid; these ARE deletable from the console)
CREATE TABLE IF NOT EXISTS registrations (
  id             INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  reference      VARCHAR(16)  NOT NULL UNIQUE,
  source         ENUM('web','staff') NOT NULL DEFAULT 'web',
  status         ENUM('pending','paid','failed') NOT NULL DEFAULT 'pending',
  category       ENUM('student','doctor','bni','general') NOT NULL,
  name           VARCHAR(100) NOT NULL,
  address        VARCHAR(300) NOT NULL,
  mobile         VARCHAR(10)  NOT NULL,
  email          VARCHAR(150) NOT NULL,
  year_of_study  VARCHAR(12)  NULL,
  college        VARCHAR(150) NULL,
  competitions   VARCHAR(60)  NULL,
  chapter        VARCHAR(100) NULL,
  organisation   VARCHAR(150) NULL,
  qualification  VARCHAR(100) NULL,
  clinic         VARCHAR(150) NULL,
  coupon_code    VARCHAR(30)  NULL,
  base_paise     INT UNSIGNED NOT NULL,
  discount_paise INT UNSIGNED NOT NULL DEFAULT 0,
  amount_paise   INT UNSIGNED NOT NULL,
  ip             VARCHAR(45)  NULL,
  created_at     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  paid_at        DATETIME NULL,
  KEY idx_reg_mobile (mobile),
  KEY idx_reg_status (status),
  KEY idx_reg_ip (ip, created_at),
  KEY idx_reg_coupon (coupon_code, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- One row per Coaxlure order created for a registration (a retry = a new row).
-- 'failed' attempts are written ONLY by the abandoned-payment sweep in
-- api/_bootstrap.php (owli_fail_stale_attempts) after one final Coaxlure
-- check confirms no money ever arrived — the attempt's history row stays.
CREATE TABLE IF NOT EXISTS payment_attempts (
  id              INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  registration_id INT UNSIGNED NOT NULL,
  coax_token      VARCHAR(200) NOT NULL,
  coax_order_id   VARCHAR(100) NULL,
  amount_paise    INT UNSIGNED NOT NULL,
  status          ENUM('pending','paid','failed') NOT NULL DEFAULT 'pending',
  raw_status      VARCHAR(120) NULL,
  created_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  last_checked_at DATETIME NULL,
  verified_at     DATETIME NULL,
  KEY idx_pa_reg (registration_id),
  KEY idx_pa_status (status, created_at),
  CONSTRAINT fk_pa_reg FOREIGN KEY (registration_id) REFERENCES registrations(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Starting prices (INR): Students 299, BNI 399, Doctors 399, General 449
-- ------------------------------------------------------------
INSERT INTO tiers (id, category, label, amount_paise) VALUES
  (1, 'student', 'Standard', 29900),
  (2, 'bni',     'Standard', 39900),
  (3, 'doctor',  'Standard', 39900),
  (4, 'general', 'Standard', 44900)
ON DUPLICATE KEY UPDATE id = id;

-- ------------------------------------------------------------
-- HOW TO CHANGE PRICES / ADD OFFERS LATER (run in phpMyAdmin -> SQL)
--
-- Change a price:
--   UPDATE tiers SET amount_paise = 49900 WHERE id = 4;
--
-- Early-bird for students (Rs 249 until 31 Oct 2026; beats the standard row):
--   INSERT INTO tiers (category, label, amount_paise, valid_until)
--   VALUES ('student', 'Early bird', 24900, '2026-10-31 23:59:59');
--
-- 10% coupon for everyone, 50 uses:
--   INSERT INTO coupons (code, discount_type, value, max_uses)
--   VALUES ('EARLY10', 'percent', 10, 50);
--
-- Rs 100 off for BNI members only:
--   INSERT INTO coupons (code, discount_type, value, category)
--   VALUES ('BNI100', 'flat', 10000, 'bni');
--
-- Single-use FREE ticket (100% off) — one code per guest. A Rs 0 price made
-- by a coupon is confirmed INSTANTLY on the server, with no payment page:
--   INSERT INTO coupons (code, discount_type, value, max_uses)
--   VALUES ('FREEAP26-ABC123', 'percent', 100, 1);
--
-- Switch a coupon off:
--   UPDATE coupons SET active = 0 WHERE code = 'EARLY10';
--
-- REUSABLE codes (max_uses NULL = unlimited):
--   INSERT INTO coupons (code, discount_type, value) VALUES ('EARLY10', 'percent', 10);
--   INSERT INTO coupons (code, discount_type, value) VALUES ('PAYANAM20', 'percent', 20);
--
-- COUPON RULES (see api/_bootstrap.php owli_quote()):
--   * percent 10 on Rs 299 -> Rs 30 off (whole rupees, rounded).
--   * A coupon that covers the WHOLE price makes the registration FREE:
--     confirmed instantly, no payment page (Rs 0 cannot be charged online).
--   * max_uses is counted on PAID rows only. A free (coupon-paid) row IS a
--     paid row — so a 100% code with max_uses = 1 is single-use, as intended.
--   * Codes are generated in the ADMIN CONSOLE (Coupons panel) or typed as
--     INSERTs here in phpMyAdmin — codes live in the database only. Never
--     put real coupon codes in a committed file: everything in the repo is
--     published to public_html/ (blocked at the web server by the root
--     .htaccess, but the repo itself is public to anyone with the GitHub link).
-- ------------------------------------------------------------
