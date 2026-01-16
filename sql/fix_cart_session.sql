-- Fix cart table session_id column
USE fovyarnx_cusut;

-- Check current cart table structure
DESCRIBE cart;

-- Add session_id column if it doesn't exist
SET @col_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = 'fovyarnx_cusut' 
    AND TABLE_NAME = 'cart' 
    AND COLUMN_NAME = 'session_id'
);

-- Add session_id column if it doesn't exist
ALTER TABLE cart 
ADD COLUMN IF NOT EXISTS session_id VARCHAR(128) DEFAULT NULL;

-- Modify columns to ensure proper configuration
ALTER TABLE cart 
MODIFY COLUMN session_id VARCHAR(128) DEFAULT NULL,
MODIFY COLUMN user_id INT DEFAULT NULL;

-- Drop existing unique constraint if exists
ALTER TABLE cart DROP INDEX IF EXISTS unique_product;

-- Add new unique constraint
ALTER TABLE cart 
ADD UNIQUE KEY unique_product (user_id, session_id, product_id);

-- Clean up any orphaned cart items (optional)
-- DELETE FROM cart WHERE user_id IS NULL AND (session_id IS NULL OR session_id = '');

SELECT 'Cart table fixed successfully!' as status;
