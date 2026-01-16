-- Simple Cart Table Fix
-- Run this in phpMyAdmin SQL tab

USE fovyarnx_cusut;

-- Step 1: Modify columns to allow NULL
ALTER TABLE cart 
MODIFY COLUMN session_id VARCHAR(128) DEFAULT NULL;

ALTER TABLE cart 
MODIFY COLUMN user_id INT DEFAULT NULL;

-- Step 2: Drop old unique constraint (ignore error if doesn't exist)
ALTER TABLE cart DROP INDEX unique_product;

-- Step 3: Add new unique constraint
ALTER TABLE cart 
ADD UNIQUE KEY unique_product (user_id, session_id, product_id);

-- Verify the fix
SELECT 'Cart table fixed successfully!' as status;
DESCRIBE cart;
