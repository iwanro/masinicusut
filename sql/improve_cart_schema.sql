-- =====================================================
-- Improve Cart Table Schema
-- SUNDARI TOP STAR S.R.L.
-- Adds validation and cleanup mechanisms for cart system
-- =====================================================

-- Use the correct database
-- NOTE: Replace 'fovyarnx_cusut' with your actual database name
USE fovyarnx_cusut;

-- =====================================================
-- 1. Ensure proper column definitions
-- =====================================================

-- Make sure session_id allows NULL and has sufficient length
ALTER TABLE cart
MODIFY COLUMN session_id VARCHAR(128) DEFAULT NULL;

-- Make sure user_id allows NULL
ALTER TABLE cart
MODIFY COLUMN user_id INT DEFAULT NULL;

-- =====================================================
-- 2. Add validation trigger (prevents both user_id and session_id being NULL)
-- =====================================================

DELIMITER //

CREATE TRIGGER validate_cart_insert
BEFORE INSERT ON cart
FOR EACH ROW
BEGIN
    IF NEW.user_id IS NULL AND (NEW.session_id IS NULL OR NEW.session_id = '') THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Cart item must have either user_id or session_id';
    END IF;
END//

CREATE TRIGGER validate_cart_update
BEFORE UPDATE ON cart
FOR EACH ROW
BEGIN
    IF NEW.user_id IS NULL AND (NEW.session_id IS NULL OR NEW.session_id = '') THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Cart item must have either user_id or session_id';
    END IF;
END//

DELIMITER ;

-- =====================================================
-- 3. Ensure unique constraint allows NULLs correctly
-- MySQL treats NULLs as distinct in UNIQUE constraints, which is what we want
-- But we need to drop and recreate if it doesn't exist
-- =====================================================

-- Drop existing unique constraint if exists
ALTER TABLE cart DROP INDEX IF EXISTS unique_product;

-- Add unique constraint that allows NULLs
ALTER TABLE cart
ADD UNIQUE KEY unique_product (user_id, session_id, product_id);

-- =====================================================
-- 4. Create function for cart migration (user login)
-- =====================================================

DELIMITER //

CREATE PROCEDURE migrate_cart_to_user(
    IN p_session_id VARCHAR(128),
    IN p_user_id INT
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        -- Log error but don't fail
        GET DIAGNOSTICS CONDITION 1 @err_no = MYSQL_ERRNO, @err_msg = MESSAGE_TEXT;
        INSERT INTO cart_migration_log (session_id, user_id, success, error_message)
        VALUES (p_session_id, p_user_id, 0, CONCAT(@err_no, ': ', @err_msg));
    END;

    -- Update existing cart items: set user_id, clear session_id
    UPDATE cart
    SET user_id = p_user_id,
        session_id = NULL,
        updated_at = CURRENT_TIMESTAMP
    WHERE session_id = p_session_id
      AND (user_id IS NULL OR user_id = 0);

    -- Log successful migration
    INSERT INTO cart_migration_log (session_id, user_id, success, migrated_items)
    VALUES (p_session_id, p_user_id, 1, ROW_COUNT());

    SELECT ROW_COUNT() AS migrated_items;
END//

DELIMITER ;

-- =====================================================
-- 5. Create cart migration log table (for debugging)
-- =====================================================

CREATE TABLE IF NOT EXISTS cart_migration_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(128),
    user_id INT,
    success TINYINT(1) DEFAULT 1,
    migrated_items INT DEFAULT 0,
    error_message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_session (session_id),
    INDEX idx_user (user_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 6. Create cleanup procedure for old cart entries
-- =====================================================

DELIMITER //

CREATE PROCEDURE cleanup_old_carts(
    IN p_days_old INT
)
BEGIN
    DECLARE rows_deleted INT DEFAULT 0;

    -- Delete cart entries with session_id older than specified days
    -- where user_id is NULL (guest carts)
    DELETE FROM cart
    WHERE user_id IS NULL
      AND session_id IS NOT NULL
      AND updated_at < DATE_SUB(NOW(), INTERVAL p_days_old DAY);

    SET rows_deleted = ROW_COUNT();

    -- Log cleanup
    INSERT INTO cart_cleanup_log (days_old, deleted_entries, cleanup_date)
    VALUES (p_days_old, rows_deleted, NOW());

    SELECT rows_deleted AS deleted_entries;
END//

DELIMITER ;

-- =====================================================
-- 7. Create cart cleanup log table
-- =====================================================

CREATE TABLE IF NOT EXISTS cart_cleanup_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    days_old INT NOT NULL,
    deleted_entries INT DEFAULT 0,
    cleanup_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_cleanup_date (cleanup_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 8. Verification queries
-- =====================================================

-- Check table structure
SHOW CREATE TABLE cart;

-- Check triggers
SHOW TRIGGERS LIKE 'cart';

-- Check procedures
SHOW PROCEDURE STATUS WHERE Db = DATABASE();

-- =====================================================
-- 9. Migration complete message
-- =====================================================

SELECT 'Cart schema improvements applied successfully!' AS status;