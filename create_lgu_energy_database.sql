-- SQL to create the lgu-energy database and users table
CREATE DATABASE IF NOT EXISTS `lgu-energy`;
USE `lgu-energy`;

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
	`remember_token` VARCHAR(100) DEFAULT NULL,
	`created_at` TIMESTAMP NULL DEFAULT NULL,
	`updated_at` TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
