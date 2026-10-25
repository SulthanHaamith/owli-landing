-- Price update: student ₹299 → ₹349, general ₹10 (test) → ₹449
-- Target DB: u810328541_owlievents on Hostinger (phpMyAdmin → SQL tab).
-- Money is stored as paise integers: ₹349 = 34900, ₹449 = 44900.
--
-- Format follows GO-LIVE-STEPS.md (§A1 price change, §C1 price restore):
-- each UPDATE as its own block, each with a "Check it" verification line.
-- Instant — no deploy needed. Repo docs (schema.sql, PRD.md §4.5,
-- README.md §10) already reflect these values.

UPDATE tiers SET amount_paise = 34900 WHERE category = 'student';

Check it: SELECT category, amount_paise FROM tiers; → student now 34900.

UPDATE tiers SET amount_paise = 44900 WHERE category = 'general';

Check it: SELECT category, amount_paise FROM tiers; → general now 44900.

-- Verify from the public API afterwards (~instant):
--   https://owlihealthcare.com/api/quote.php?category=all
--   → {"student":349,"doctor":399,"bni":399,"general":449}