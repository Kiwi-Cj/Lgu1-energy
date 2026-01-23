
-- SQL structure for lgu-energy database
CREATE DATABASE IF NOT EXISTS `lgu-energy`;
USE `lgu-energy`;

-- Table: users
CREATE TABLE IF NOT EXISTS `users` (
	`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
	`name` VARCHAR(255) NOT NULL,
	`first_name` VARCHAR(255) NOT NULL,
	`middle_name` VARCHAR(255) DEFAULT NULL,
	`last_name` VARCHAR(255) NOT NULL,
	`suffix` VARCHAR(50) DEFAULT NULL,
	`religion` VARCHAR(100) DEFAULT NULL,
	`nationality` VARCHAR(100) DEFAULT NULL,
	`address` VARCHAR(255) NOT NULL,
	`gender` ENUM('male','female','other') NOT NULL,
	`date_of_birth` DATE NOT NULL,
	`place_of_birth` VARCHAR(255) NOT NULL,
	`email` VARCHAR(255) NOT NULL UNIQUE,
	`phone` VARCHAR(20) NOT NULL,
	`email_verified_at` TIMESTAMP NULL DEFAULT NULL,
	`password` VARCHAR(255) NOT NULL,
	`role` ENUM('admin','staff') NOT NULL DEFAULT 'staff',
	`status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
	`remember_token` VARCHAR(100) DEFAULT NULL,
	`created_at` TIMESTAMP NULL DEFAULT NULL,
	`updated_at` TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: facilities
CREATE TABLE IF NOT EXISTS `facilities` (
	`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
	`project_id` BIGINT UNSIGNED DEFAULT NULL,
	`name` VARCHAR(255) NOT NULL,
	`location` VARCHAR(255) NOT NULL,
	`type` VARCHAR(255) NOT NULL,
	`status` VARCHAR(255) NOT NULL,
	`energy_profile` VARCHAR(255) DEFAULT NULL,
	`description` TEXT DEFAULT NULL,
	`contact_person` VARCHAR(255) DEFAULT NULL,
	`capacity` INT DEFAULT NULL,
	`image` VARCHAR(255) DEFAULT NULL,
	`created_at` TIMESTAMP NULL DEFAULT NULL,
	`updated_at` TIMESTAMP NULL DEFAULT NULL,
	FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: equipment
CREATE TABLE IF NOT EXISTS `equipment` (
	`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
	`facility_id` BIGINT UNSIGNED NOT NULL,
	`name` VARCHAR(255) NOT NULL,
	`type` VARCHAR(255) NOT NULL,
	`serial_no` VARCHAR(255) DEFAULT NULL,
	`status` VARCHAR(255) NOT NULL,
	`last_maintenance` DATE DEFAULT NULL,
	`created_at` TIMESTAMP NULL DEFAULT NULL,
	`updated_at` TIMESTAMP NULL DEFAULT NULL,
	FOREIGN KEY (`facility_id`) REFERENCES `facilities`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: consumptions
CREATE TABLE IF NOT EXISTS `consumptions` (
	`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
	`project_id` BIGINT UNSIGNED DEFAULT NULL,
	`facility_id` BIGINT UNSIGNED NOT NULL,
	`month` VARCHAR(7) NOT NULL,
	`kwh` DECIMAL(12,2) NOT NULL,
	`peak_load` DECIMAL(12,2) DEFAULT NULL,
	`created_at` TIMESTAMP NULL DEFAULT NULL,
	`updated_at` TIMESTAMP NULL DEFAULT NULL,
	FOREIGN KEY (`facility_id`) REFERENCES `facilities`(`id`) ON DELETE CASCADE,
	FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: energy_usages

CREATE TABLE IF NOT EXISTS `energy_usages` (
	`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
	`facility_id` BIGINT UNSIGNED NOT NULL,
	`equipment_id` BIGINT UNSIGNED DEFAULT NULL,
	`date` DATE NOT NULL,
	`usage` DECIMAL(10,2) NOT NULL,
	`peak` DECIMAL(10,2) DEFAULT NULL,
	`source` VARCHAR(255) DEFAULT NULL,
	`created_at` TIMESTAMP NULL DEFAULT NULL,
	`updated_at` TIMESTAMP NULL DEFAULT NULL,
	FOREIGN KEY (`facility_id`) REFERENCES `facilities`(`id`) ON DELETE CASCADE,
	FOREIGN KEY (`equipment_id`) REFERENCES `equipment`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: cache
CREATE TABLE IF NOT EXISTS `cache` (
	`key` VARCHAR(255) NOT NULL PRIMARY KEY,
	`value` MEDIUMTEXT NOT NULL,
	`expiration` INT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: cache_locks
CREATE TABLE IF NOT EXISTS `cache_locks` (
	`key` VARCHAR(255) NOT NULL PRIMARY KEY,
	`owner` VARCHAR(255) NOT NULL,
	`expiration` INT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: jobs
CREATE TABLE IF NOT EXISTS `jobs` (
	`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
	`queue` VARCHAR(255) NOT NULL,
	`payload` LONGTEXT NOT NULL,
	`attempts` TINYINT UNSIGNED NOT NULL,
	`reserved_at` INT UNSIGNED DEFAULT NULL,
	`available_at` INT UNSIGNED NOT NULL,
	`created_at` INT UNSIGNED NOT NULL,
	INDEX (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: job_batches
CREATE TABLE IF NOT EXISTS `job_batches` (
	`id` VARCHAR(255) NOT NULL PRIMARY KEY,
	`name` VARCHAR(255) NOT NULL,
	`total_jobs` INT NOT NULL,
	`pending_jobs` INT NOT NULL,
	`failed_jobs` INT NOT NULL,
	`failed_job_ids` LONGTEXT NOT NULL,
	`options` MEDIUMTEXT DEFAULT NULL,
	`cancelled_at` INT DEFAULT NULL,
	`created_at` INT NOT NULL,
	`finished_at` INT DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: failed_jobs
CREATE TABLE IF NOT EXISTS `failed_jobs` (
	`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
	`uuid` VARCHAR(255) NOT NULL UNIQUE,
	`connection` TEXT NOT NULL,
	`queue` TEXT NOT NULL,
	`payload` LONGTEXT NOT NULL,
	`exception` LONGTEXT NOT NULL,
	`failed_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: invoices
CREATE TABLE IF NOT EXISTS `invoices` (
	`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
	`project_id` BIGINT UNSIGNED DEFAULT NULL,
	`user_id` BIGINT UNSIGNED NOT NULL,
	`facility_id` BIGINT UNSIGNED DEFAULT NULL,
	`invoice_no` VARCHAR(255) NOT NULL UNIQUE,
	`reference_no` VARCHAR(255) DEFAULT NULL,
	`billing_start` DATE DEFAULT NULL,
	`billing_end` DATE DEFAULT NULL,
	`due_date` DATE NOT NULL,
	`amount` DECIMAL(12,2) NOT NULL,
	`status` ENUM('paid','unpaid','overdue') NOT NULL DEFAULT 'unpaid',
	`paid_date` DATE DEFAULT NULL,
	`notes` TEXT DEFAULT NULL,
	`created_at` TIMESTAMP NULL DEFAULT NULL,
	`updated_at` TIMESTAMP NULL DEFAULT NULL,
	FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
	FOREIGN KEY (`facility_id`) REFERENCES `facilities`(`id`) ON DELETE SET NULL,
	FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: payments
CREATE TABLE IF NOT EXISTS `payments` (
	`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
	`project_id` BIGINT UNSIGNED DEFAULT NULL,
	`facility_id` BIGINT UNSIGNED DEFAULT NULL,
	`invoice_id` BIGINT UNSIGNED NOT NULL,
	`user_id` BIGINT UNSIGNED NOT NULL,
	`payment_no` VARCHAR(255) NOT NULL UNIQUE,
	`date` DATE NOT NULL,
	`amount` DECIMAL(12,2) NOT NULL,
	`method` ENUM('cash','bank','online') NOT NULL DEFAULT 'cash',
	`status` ENUM('pending','confirmed') NOT NULL DEFAULT 'pending',
	`reference_no` VARCHAR(255) DEFAULT NULL,
	`notes` TEXT DEFAULT NULL,
	`created_at` TIMESTAMP NULL DEFAULT NULL,
	`updated_at` TIMESTAMP NULL DEFAULT NULL,
	FOREIGN KEY (`invoice_id`) REFERENCES `invoices`(`id`) ON DELETE CASCADE,
	FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
	FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`) ON DELETE SET NULL,
	FOREIGN KEY (`facility_id`) REFERENCES `facilities`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: breakdowns
CREATE TABLE IF NOT EXISTS `breakdowns` (
	`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
	`invoice_id` BIGINT UNSIGNED NOT NULL,
	`bill_no` VARCHAR(255) NOT NULL,
	`energy` DECIMAL(12,2) NOT NULL DEFAULT 0,
	`water` DECIMAL(12,2) NOT NULL DEFAULT 0,
	`other` DECIMAL(12,2) NOT NULL DEFAULT 0,
	`taxes` DECIMAL(12,2) NOT NULL DEFAULT 0,
	`total` DECIMAL(12,2) NOT NULL,
	`created_at` TIMESTAMP NULL DEFAULT NULL,
	`updated_at` TIMESTAMP NULL DEFAULT NULL,
	FOREIGN KEY (`invoice_id`) REFERENCES `invoices`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: billing_history
CREATE TABLE IF NOT EXISTS `billing_history` (
	`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
	`invoice_id` BIGINT UNSIGNED NOT NULL,
	`bill_no` VARCHAR(255) NOT NULL,
	`billing_start` DATE NOT NULL,
	`billing_end` DATE NOT NULL,
	`due_date` DATE NOT NULL,
	`amount` DECIMAL(12,2) NOT NULL,
	`status` ENUM('paid','unpaid','overdue') NOT NULL DEFAULT 'unpaid',
	`paid_date` DATE DEFAULT NULL,
	`created_at` TIMESTAMP NULL DEFAULT NULL,
	`updated_at` TIMESTAMP NULL DEFAULT NULL,
	FOREIGN KEY (`invoice_id`) REFERENCES `invoices`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: projects
CREATE TABLE IF NOT EXISTS `projects` (
	`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
	`name` VARCHAR(255) NOT NULL,
	`description` TEXT DEFAULT NULL,
	`start_date` DATE NOT NULL,
	`end_date` DATE DEFAULT NULL,
	`beneficiaries` INT DEFAULT NULL,
	`energy_saved` VARCHAR(255) DEFAULT NULL,
	`co2_reduction` VARCHAR(255) DEFAULT NULL,
	`created_at` TIMESTAMP NULL DEFAULT NULL,
	`updated_at` TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: maintenance_logs
CREATE TABLE IF NOT EXISTS `maintenance_logs` (
	`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
	`equipment_id` BIGINT UNSIGNED NOT NULL,
	`date` DATE NOT NULL,
	`type` VARCHAR(255) NOT NULL,
	`remarks` TEXT DEFAULT NULL,
	`status` VARCHAR(255) NOT NULL,
	`created_at` TIMESTAMP NULL DEFAULT NULL,
	`updated_at` TIMESTAMP NULL DEFAULT NULL,
	FOREIGN KEY (`equipment_id`) REFERENCES `equipment`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: maintenance_schedules
CREATE TABLE IF NOT EXISTS `maintenance_schedules` (
	`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
	`equipment_id` BIGINT UNSIGNED NOT NULL,
	`type` VARCHAR(255) NOT NULL,
	`scheduled_date` DATE NOT NULL,
	`status` VARCHAR(255) NOT NULL DEFAULT 'Pending',
	`remarks` TEXT DEFAULT NULL,
	`created_at` TIMESTAMP NULL DEFAULT NULL,
	`updated_at` TIMESTAMP NULL DEFAULT NULL,
	FOREIGN KEY (`equipment_id`) REFERENCES `equipment`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
