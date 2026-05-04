-- ============================================================
-- AgriLease Database Migration
-- Run this on your EXISTING agrilease_v2 database.
-- Safe to run: all changes use IF NOT EXISTS / IF EXISTS guards.
-- ============================================================

USE agrilease_v2;

-- ------------------------------------------------------------
-- 1. products table
--    Add missing index on `availability` column
-- ------------------------------------------------------------
SET @idx := (
    SELECT COUNT(1) FROM information_schema.STATISTICS
    WHERE table_schema = 'agrilease_v2'
      AND table_name   = 'products'
      AND index_name   = 'idx_availability'
);
SET @sql := IF(@idx = 0,
    'ALTER TABLE products ADD INDEX idx_availability (availability)',
    'SELECT "idx_availability already exists, skipping" AS info'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;


-- ------------------------------------------------------------
-- 2. bookings table
--    Add missing index on `payment_status` column
-- ------------------------------------------------------------
SET @idx := (
    SELECT COUNT(1) FROM information_schema.STATISTICS
    WHERE table_schema = 'agrilease_v2'
      AND table_name   = 'bookings'
      AND index_name   = 'idx_payment_status'
);
SET @sql := IF(@idx = 0,
    'ALTER TABLE bookings ADD INDEX idx_payment_status (payment_status)',
    'SELECT "idx_payment_status already exists, skipping" AS info'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;


-- ------------------------------------------------------------
-- 3. bookings table
--    Add composite index used by the overlap-check query in book.php
-- ------------------------------------------------------------
SET @idx := (
    SELECT COUNT(1) FROM information_schema.STATISTICS
    WHERE table_schema = 'agrilease_v2'
      AND table_name   = 'bookings'
      AND index_name   = 'idx_booking_status_dates'
);
SET @sql := IF(@idx = 0,
    'ALTER TABLE bookings ADD INDEX idx_booking_status_dates (status, start_date, end_date)',
    'SELECT "idx_booking_status_dates already exists, skipping" AS info'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;


-- ------------------------------------------------------------
-- 4. products table
--    Add composite index used by products.php filter queries
-- ------------------------------------------------------------
SET @idx := (
    SELECT COUNT(1) FROM information_schema.STATISTICS
    WHERE table_schema = 'agrilease_v2'
      AND table_name   = 'products'
      AND index_name   = 'idx_product_status_category'
);
SET @sql := IF(@idx = 0,
    'ALTER TABLE products ADD INDEX idx_product_status_category (status, category)',
    'SELECT "idx_product_status_category already exists, skipping" AS info'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;


-- ------------------------------------------------------------
-- 5. receipts table
--    Add composite index on payment_status + payment_date
-- ------------------------------------------------------------
SET @idx := (
    SELECT COUNT(1) FROM information_schema.STATISTICS
    WHERE table_schema = 'agrilease_v2'
      AND table_name   = 'receipts'
      AND index_name   = 'idx_receipt_payment'
);
SET @sql := IF(@idx = 0,
    'ALTER TABLE receipts ADD INDEX idx_receipt_payment (payment_status, payment_date)',
    'SELECT "idx_receipt_payment already exists, skipping" AS info'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;


-- ------------------------------------------------------------
-- 6. Fix existing data inconsistency:
--    Sync `availability` column to match `status` for any rows
--    where they got out of sync.
-- ------------------------------------------------------------
UPDATE products
SET availability = CASE
    WHEN status = 'available'    THEN 'Available'
    WHEN status = 'booked'       THEN 'Rented'
    WHEN status = 'maintenance'  THEN 'Maintenance'
    ELSE availability
END
WHERE
    (status = 'available'   AND availability != 'Available')   OR
    (status = 'booked'      AND availability != 'Rented')      OR
    (status = 'maintenance' AND availability != 'Maintenance');


-- ------------------------------------------------------------
-- 7. Fix existing data inconsistency:
--    Any product marked 'booked' but with NO active/confirmed
--    booking should be reset to 'available'.
--    (Caused by the old bug where book.php set status='booked'
--     before the owner confirmed.)
-- ------------------------------------------------------------
UPDATE products p
SET
    p.status       = 'available',
    p.availability = 'Available'
WHERE
    p.status = 'booked'
    AND NOT EXISTS (
        SELECT 1 FROM bookings b
        WHERE b.product_id = p.id
          AND b.status IN ('pending', 'confirmed')
    );


-- ------------------------------------------------------------
-- 8. Verify triggers exist — recreate if missing
--    (Only needed if you set up the DB without the full SQL)
-- ------------------------------------------------------------

-- Trigger: before_booking_insert
DROP TRIGGER IF EXISTS before_booking_insert;
DELIMITER $$
CREATE TRIGGER before_booking_insert
BEFORE INSERT ON bookings
FOR EACH ROW
BEGIN
    SET NEW.deposit_amount = NEW.total_price * 0.30;
    SET NEW.final_amount   = NEW.total_price * 0.70;
END$$
DELIMITER ;


-- Trigger: after_payment_completed
DROP TRIGGER IF EXISTS after_payment_completed;
DELIMITER $$
CREATE TRIGGER after_payment_completed
AFTER UPDATE ON payments
FOR EACH ROW
BEGIN
    IF NEW.payment_status = 'completed' AND OLD.payment_status != 'completed' THEN
        IF NEW.payment_type = 'deposit' THEN
            UPDATE bookings
            SET deposit_paid      = 1,
                deposit_paid_date = NEW.payment_date,
                payment_status    = 'pending'
            WHERE id = NEW.booking_id;
        ELSEIF NEW.payment_type = 'final' THEN
            UPDATE bookings
            SET final_paid      = 1,
                final_paid_date = NEW.payment_date,
                payment_status  = 'paid'
            WHERE id = NEW.booking_id;
        ELSEIF NEW.payment_type = 'full' THEN
            UPDATE bookings
            SET deposit_paid      = 1,
                deposit_paid_date = NEW.payment_date,
                final_paid        = 1,
                final_paid_date   = NEW.payment_date,
                payment_status    = 'paid'
            WHERE id = NEW.booking_id;
        END IF;
    END IF;
END$$
DELIMITER ;


-- Trigger: after_booking_confirmed
DROP TRIGGER IF EXISTS after_booking_confirmed;
DELIMITER $$
CREATE TRIGGER after_booking_confirmed
AFTER UPDATE ON bookings
FOR EACH ROW
BEGIN
    IF NEW.status = 'confirmed' AND OLD.status != 'confirmed' THEN
        IF NOT EXISTS (SELECT 1 FROM receipts WHERE booking_id = NEW.id) THEN
            INSERT INTO receipts (
                booking_id, receipt_number, subtotal, tax_amount, discount_amount,
                total_amount, deposit_amount, final_amount,
                deposit_paid, final_paid, payment_status
            ) VALUES (
                NEW.id,
                CONCAT('RCP-', LPAD(NEW.id, 8, '0')),
                NEW.total_price,
                NEW.total_price * 0.18,
                0,
                NEW.total_price * 1.18,
                NEW.deposit_amount,
                NEW.final_amount,
                NEW.deposit_paid,
                NEW.final_paid,
                CASE
                    WHEN NEW.deposit_paid = 1 AND NEW.final_paid = 1 THEN 'paid'
                    WHEN NEW.deposit_paid = 1                        THEN 'partial'
                    ELSE 'pending'
                END
            );
        END IF;
    END IF;
END$$
DELIMITER ;


-- ------------------------------------------------------------
-- 9. Generate missing receipts for bookings that were already
--    confirmed but have no receipt row yet.
--    (Handles cases where the trigger wasn't in place earlier.)
-- ------------------------------------------------------------
INSERT INTO receipts (
    booking_id, receipt_number, subtotal, tax_amount, discount_amount,
    total_amount, deposit_amount, final_amount,
    deposit_paid, final_paid, payment_status
)
SELECT
    b.id,
    CONCAT('RCP-', LPAD(b.id, 8, '0')),
    b.total_price,
    b.total_price * 0.18,
    0,
    b.total_price * 1.18,
    b.deposit_amount,
    b.final_amount,
    b.deposit_paid,
    b.final_paid,
    CASE
        WHEN b.deposit_paid = 1 AND b.final_paid = 1 THEN 'paid'
        WHEN b.deposit_paid = 1                      THEN 'partial'
        ELSE 'pending'
    END
FROM bookings b
WHERE b.status = 'confirmed'
  AND NOT EXISTS (
      SELECT 1 FROM receipts r WHERE r.booking_id = b.id
  );


-- ------------------------------------------------------------
-- Done. Run: SELECT "Migration complete!" AS result;
-- ------------------------------------------------------------
SELECT 'Migration complete!' AS result;
