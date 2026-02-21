-- Migration: Add Download Tracking and Campaign Supporters Gallery
-- Date: 2026-02-22
-- Description: Adds support for tracking download statistics and storing campaign supporter thumbnails

-- Step 1: Add download_count column to frames table
ALTER TABLE `frames` 
ADD COLUMN `download_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'Number of times this frame was downloaded' AFTER `slot_count`;

-- Step 2: Create frame_supporters table to track supporter thumbnails
CREATE TABLE IF NOT EXISTS `frame_supporters` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `frame_id` int(11) NOT NULL,
  `thumbnail_path` varchar(500) NOT NULL COMMENT 'Path to supporter thumbnail image',
  `thumbnail_size` int(11) NOT NULL COMMENT 'File size in bytes',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `frame_id` (`frame_id`),
  KEY `created_at` (`created_at`),
  CONSTRAINT `frame_supporters_ibfk_1` FOREIGN KEY (`frame_id`) REFERENCES `frames` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Stores thumbnails of campaign supporters';

-- Step 3: Create index for better performance when fetching recent supporters
CREATE INDEX `idx_frame_created` ON `frame_supporters` (`frame_id`, `created_at` DESC);

-- Migration complete
-- Next steps:
-- 1. Run this SQL file in phpMyAdmin or MySQL console
-- 2. Ensure uploads/supporters/thumbs/ directory exists with write permissions
-- 3. Test the new features
