-- Add short_url field to frames table
-- This stores the shortened URL created at albn.org (or other shortener service)

ALTER TABLE `frames` 
ADD COLUMN `short_url` varchar(255) DEFAULT NULL COMMENT 'Shortened URL from albn.org or similar service' AFTER `slot_count`;

-- Add index for quick lookups
ALTER TABLE `frames`
ADD INDEX `idx_short_url` (`short_url`);
