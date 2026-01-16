-- =====================================================
-- Database Migration - Fix Production Schema
-- SUNDARI TOP STAR S.R.L.
-- =====================================================
--
-- IMPORTANT: Run this in phpMyAdmin on your PRODUCTION database
-- This migration adds missing columns to match the codebase expectations
-- =====================================================

-- Check if 'featured' column exists and rename it to 'is_featured'
-- If your production database already has 'featured' column, this renames it
-- If you already have 'is_featured', this will fail safely (just skip that line)

-- Step 1: Rename 'featured' to 'is_featured' if it exists
-- NOTE: If you get error "Duplicate column name", it means 'is_featured' already exists - this is OK!
ALTER TABLE products CHANGE COLUMN featured is_featured TINYINT(1) DEFAULT 0 COMMENT 'Featured product for homepage';

-- Step 2: Add subcategory_id column (if it doesn't exist)
-- NOTE: If you get error "Duplicate column name", it means 'subcategory_id' already exists - this is OK!
ALTER TABLE products
    ADD COLUMN subcategory_id INT DEFAULT NULL AFTER category_id,
    ADD INDEX idx_subcategory (subcategory_id),
    ADD FOREIGN KEY (subcategory_id) REFERENCES categories(id) ON DELETE SET NULL;

-- Step 3: Verify the changes
SELECT
    COLUMN_NAME,
    DATA_TYPE,
    IS_NULLABLE,
    COLUMN_DEFAULT,
    COLUMN_KEY
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'products'
  AND COLUMN_NAME IN ('is_featured', 'subcategory_id', 'category_id');

-- Expected output should show:
-- is_featured        | tinyint   | YES   | 0        |
-- subcategory_id     | int       | YES   | NULL     | MUL
-- category_id        | int       | YES   | NULL     | MUL

-- =====================================================
-- Verification Queries (run these to test)
-- =====================================================

-- Test 1: Check if featured products can be queried
-- SELECT COUNT(*) as featured_count FROM products WHERE is_featured = 1 AND is_active = 1;

-- Test 2: Check if subcategory join works
-- SELECT p.name, c.name as category, sc.name as subcategory
-- FROM products p
-- LEFT JOIN categories c ON p.category_id = c.id
-- LEFT JOIN categories sc ON p.subcategory_id = sc.id
-- LIMIT 5;

-- =====================================================
-- Rollback (if something goes wrong)
-- =====================================================
-- To undo these changes:
-- ALTER TABLE products DROP FOREIGN KEY products_ibfk_2;  -- May have different name
-- ALTER TABLE products DROP INDEX idx_subcategory;
-- ALTER TABLE products DROP COLUMN subcategory_id;
-- ALTER TABLE products CHANGE COLUMN is_featured featured TINYINT(1) DEFAULT 0;
