-- DATABASE UPDATE FOR MULTI-PHOTO FRAME FEATURE
-- Run this in phpMyAdmin on your existing alburhan_frames database

-- Step 1: Add new columns to frames table
ALTER TABLE `frames` 
ADD COLUMN `is_multi_photo` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Does this frame support multiple photos' AFTER `uploaded_by`,
ADD COLUMN `slot_count` int(11) NOT NULL DEFAULT 1 COMMENT 'Number of photo slots' AFTER `is_multi_photo`;

-- Step 2: Create frame_slots table
CREATE TABLE IF NOT EXISTS `frame_slots` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `frame_id` int(11) NOT NULL,
  `slot_number` int(11) NOT NULL,
  `x_position` int(11) NOT NULL COMMENT 'X coordinate of slot center in pixels',
  `y_position` int(11) NOT NULL COMMENT 'Y coordinate of slot center in pixels',
  `width` int(11) NOT NULL COMMENT 'Slot width in pixels',
  `height` int(11) NOT NULL COMMENT 'Slot height in pixels',
  `rotation` int(11) NOT NULL DEFAULT 0 COMMENT 'Slot rotation in degrees',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `frame_id` (`frame_id`),
  UNIQUE KEY `unique_frame_slot` (`frame_id`, `slot_number`),
  CONSTRAINT `frame_slots_ibfk_1` FOREIGN KEY (`frame_id`) REFERENCES `frames` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Done! Your database is now ready for multi-photo frames.
