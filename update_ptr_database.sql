-- Database Update Script for PTR Enhancement
-- Add columns to track failed PTRs and failure reasons

-- Add failure tracking columns to cancel_booking table
ALTER TABLE `cancel_booking` 
ADD COLUMN `failure_reason` TEXT NULL COMMENT 'Reason for PTR failure' AFTER `ptr_status`,
ADD COLUMN `failed_at` DATETIME NULL COMMENT 'When PTR was marked as failed' AFTER `failure_reason`;

-- Update existing failed PTRs that might be stuck
UPDATE `cancel_booking` 
SET `failure_reason` = 'Legacy stuck PTR - needs manual review',
    `failed_at` = NOW()
WHERE `ptr_status` = 'InProcess' 
AND TIMESTAMPDIFF(HOUR, `created_date`, NOW()) > 24;

-- Add index for better performance on PTR status queries
CREATE INDEX `idx_ptr_status_created` ON `cancel_booking` (`ptr_status`, `created_date`);
CREATE INDEX `idx_ptr_id_mf_ref` ON `cancel_booking` (`ptr_id`, `mf_ref_num`);

-- Show current status of PTRs
SELECT 
    ptr_status,
    COUNT(*) as count,
    MIN(created_date) as oldest_ptr,
    MAX(created_date) as newest_ptr
FROM cancel_booking 
GROUP BY ptr_status
ORDER BY count DESC; 