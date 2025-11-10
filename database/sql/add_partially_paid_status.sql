-- Add 'partially_paid' status to fee_vouchers table
-- Run this SQL directly on your database

-- First, check if any vouchers have status values that we need to preserve
-- This query is just for information, not required to run
-- SELECT status, COUNT(*) as count FROM fee_vouchers GROUP BY status;

-- Modify the status column to include 'partially_paid'
ALTER TABLE fee_vouchers 
MODIFY COLUMN status ENUM('paid', 'unpaid', 'partially_paid', 'cancelled') 
DEFAULT 'unpaid';

-- Optional: Add a comment to the status column for documentation
ALTER TABLE fee_vouchers 
MODIFY COLUMN status ENUM('paid', 'unpaid', 'partially_paid', 'cancelled') 
DEFAULT 'unpaid' 
COMMENT 'Voucher payment status: paid (fully paid), unpaid (not paid), partially_paid (partial payment received), cancelled (voucher cancelled)';

-- Verify the change
-- Run this to confirm the new enum values
-- SHOW COLUMNS FROM fee_vouchers LIKE 'status';

-- Note: This modification is safe and will not affect existing data
-- All existing 'paid', 'unpaid', and 'cancelled' values will remain unchanged
