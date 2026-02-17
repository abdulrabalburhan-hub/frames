-- AlBurhan Frames Database Schema
-- Tables creation for existing database

-- NOTE: Database already exists on Hostinger (u840836793_alburhan_frame)
-- Just import this file directly into your existing database via phpMyAdmin

-- Admin Users Table (supports multiple admins via backend)
CREATE TABLE IF NOT EXISTS `admin_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default admin user
-- Username: admin
-- Password: Admin@123 (bcrypt hashed)
INSERT INTO `admin_users` (`username`, `password`, `email`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@alburhan.online');

-- Frames Table
CREATE TABLE IF NOT EXISTS `frames` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `unique_id` varchar(50) NOT NULL,
  `frame_name` varchar(255) NOT NULL,
  `frame_path` varchar(500) NOT NULL,
  `thumbnail_path` varchar(500) NOT NULL,
  `file_size` int(11) NOT NULL,
  `file_type` varchar(50) NOT NULL,
  `uploaded_by` int(11) NOT NULL,
  `is_multi_photo` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Does this frame support multiple photos',
  `slot_count` int(11) NOT NULL DEFAULT 1 COMMENT 'Number of photo slots',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_id` (`unique_id`),
  KEY `uploaded_by` (`uploaded_by`),
  CONSTRAINT `frames_ibfk_1` FOREIGN KEY (`uploaded_by`) REFERENCES `admin_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Frame Slots Table (defines photo placement areas for multi-photo frames)
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

-- User Photos Table (stores composite photos)
CREATE TABLE IF NOT EXISTS `user_photos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `frame_id` int(11) NOT NULL,
  `photo_path` varchar(500) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `frame_id` (`frame_id`),
  CONSTRAINT `user_photos_ibfk_1` FOREIGN KEY (`frame_id`) REFERENCES `frames` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Admin Sessions Table
CREATE TABLE IF NOT EXISTS `admin_sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `admin_id` int(11) NOT NULL,
  `session_token` varchar(255) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `admin_id` (`admin_id`),
  CONSTRAINT `admin_sessions_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admin_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Instructions for adding additional admins:
-- Use phpMyAdmin or MySQL console to insert new admin users
-- Example SQL command:
-- INSERT INTO `admin_users` (`username`, `password`, `email`) VALUES ('newadmin', '$2y$10$hashed_password_here', 'newadmin@example.com');
-- To generate bcrypt password hash, use PHP: password_hash('your_password', PASSWORD_BCRYPT);
