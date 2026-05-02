-- LGU Energy schema dump (generated from live DB connection)
-- Database: ener_lgu
-- Generated at: 2026-02-27 15:35:31

SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `cache`;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `cache_locks`;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `contact_message_replies`;
CREATE TABLE `contact_message_replies` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `contact_message_id` bigint unsigned NOT NULL,
  `sent_by_user_id` bigint unsigned DEFAULT NULL,
  `recipient_email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attachments` json DEFAULT NULL,
  `send_status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'sent',
  `sent_at` timestamp NULL DEFAULT NULL,
  `error_message` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `contact_message_replies_contact_message_id_created_at_index` (`contact_message_id`,`created_at`),
  KEY `contact_message_replies_sent_by_user_id_index` (`sent_by_user_id`),
  KEY `contact_message_replies_send_status_index` (`send_status`),
  CONSTRAINT `contact_message_replies_contact_message_id_foreign` FOREIGN KEY (`contact_message_id`) REFERENCES `contact_messages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `contact_messages`;
CREATE TABLE `contact_messages` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `mailed_to` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `emailed_at` timestamp NULL DEFAULT NULL,
  `email_error` text COLLATE utf8mb4_unicode_ci,
  `read_at` timestamp NULL DEFAULT NULL,
  `read_by_user_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `contact_messages_read_by_user_id_index` (`read_by_user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `energy_actions`;
CREATE TABLE `energy_actions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `facility_id` bigint unsigned NOT NULL,
  `action_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `priority` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `target_date` date NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Active',
  `risk_score` decimal(6,2) DEFAULT NULL,
  `alert_level` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `trigger_reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `current_kwh` decimal(12,2) DEFAULT NULL,
  `baseline_kwh` decimal(12,2) DEFAULT NULL,
  `deviation` decimal(6,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `energy_actions_facility_id_foreign` (`facility_id`),
  CONSTRAINT `energy_actions_facility_id_foreign` FOREIGN KEY (`facility_id`) REFERENCES `facilities` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `energy_incident_histories`;
CREATE TABLE `energy_incident_histories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `energy_record_id` bigint unsigned NOT NULL,
  `alert_level` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `deviation` decimal(6,2) NOT NULL,
  `date_detected` date NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Open',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `energy_incident_histories_energy_record_id_foreign` (`energy_record_id`),
  CONSTRAINT `energy_incident_histories_energy_record_id_foreign` FOREIGN KEY (`energy_record_id`) REFERENCES `energy_records` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `energy_incidents`;
CREATE TABLE `energy_incidents` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `energy_record_id` bigint unsigned DEFAULT NULL,
  `facility_id` bigint unsigned DEFAULT NULL,
  `month` int DEFAULT NULL,
  `year` int DEFAULT NULL,
  `deviation_percent` decimal(8,2) DEFAULT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Open',
  `date_detected` date NOT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `resolved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `energy_incidents_energy_record_id_foreign` (`energy_record_id`),
  CONSTRAINT `energy_incidents_energy_record_id_foreign` FOREIGN KEY (`energy_record_id`) REFERENCES `energy_records` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `energy_profiles`;
CREATE TABLE `energy_profiles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `facility_id` bigint unsigned NOT NULL,
  `primary_meter_id` bigint unsigned DEFAULT NULL,
  `electric_meter_no` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `utility_provider` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contract_account_no` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `baseline_kwh` decimal(10,2) NOT NULL,
  `engineer_approved` tinyint(1) NOT NULL DEFAULT '0',
  `baseline_locked` tinyint(1) NOT NULL DEFAULT '0',
  `baseline_source` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `main_energy_source` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `backup_power` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `transformer_capacity` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `number_of_meters` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `energy_profiles_facility_id_foreign` (`facility_id`),
  KEY `energy_profiles_primary_meter_id_index` (`primary_meter_id`),
  CONSTRAINT `energy_profiles_facility_id_foreign` FOREIGN KEY (`facility_id`) REFERENCES `facilities` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `energy_records`;
CREATE TABLE `energy_records` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `facility_id` bigint unsigned NOT NULL,
  `meter_id` bigint unsigned DEFAULT NULL,
  `year` int NOT NULL,
  `month` int NOT NULL,
  `day` int DEFAULT NULL,
  `actual_kwh` decimal(12,2) NOT NULL,
  `baseline_kwh` double DEFAULT NULL,
  `energy_cost` decimal(12,2) NOT NULL,
  `rate_per_kwh` decimal(10,2) DEFAULT NULL,
  `alert` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bill_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `recorded_by` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `deviation` decimal(8,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `energy_records_facility_id_foreign` (`facility_id`),
  KEY `energy_records_recorded_by_foreign` (`recorded_by`),
  KEY `energy_records_meter_id_index` (`meter_id`),
  CONSTRAINT `energy_records_facility_id_foreign` FOREIGN KEY (`facility_id`) REFERENCES `facilities` (`id`) ON DELETE CASCADE,
  CONSTRAINT `energy_records_recorded_by_foreign` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `facilities`;
CREATE TABLE `facilities` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `size` enum('small','medium','large','extralarge') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'small',
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `image_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `department` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `barangay` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `floor_area` double DEFAULT NULL,
  `floor_area_sqm` decimal(12,2) DEFAULT NULL,
  `floors` int DEFAULT NULL,
  `year_built` int DEFAULT NULL,
  `operating_hours` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `deleted_by` bigint unsigned DEFAULT NULL,
  `archive_reason` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `baseline_status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'collecting',
  `baseline_start_date` date DEFAULT NULL,
  `baseline_kwh` decimal(10,2) DEFAULT NULL,
  `engineer_approved` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `facilities_deleted_by_index` (`deleted_by`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `facility_audit_logs`;
CREATE TABLE `facility_audit_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `facility_id` bigint unsigned DEFAULT NULL,
  `facility_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `action` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `reason` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `performed_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `facility_audit_logs_facility_id_index` (`facility_id`),
  KEY `facility_audit_logs_action_index` (`action`),
  KEY `facility_audit_logs_performed_by_index` (`performed_by`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `facility_meters`;
CREATE TABLE `facility_meters` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `facility_id` bigint unsigned NOT NULL,
  `meter_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `meter_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meter_type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'sub',
  `parent_meter_id` bigint unsigned DEFAULT NULL,
  `location` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `multiplier` decimal(12,4) NOT NULL DEFAULT '1.0000',
  `baseline_kwh` decimal(14,2) DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `deleted_by` bigint unsigned DEFAULT NULL,
  `archive_reason` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `approved_by_user_id` bigint unsigned DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `facility_meters_facility_id_index` (`facility_id`),
  KEY `facility_meters_meter_number_index` (`meter_number`),
  KEY `facility_meters_meter_type_index` (`meter_type`),
  KEY `facility_meters_parent_meter_id_index` (`parent_meter_id`),
  KEY `facility_meters_status_index` (`status`),
  KEY `facility_meters_deleted_by_index` (`deleted_by`),
  KEY `facility_meters_approved_by_user_id_index` (`approved_by_user_id`),
  KEY `facility_meters_approved_at_index` (`approved_at`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `facility_user`;
CREATE TABLE `facility_user` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `facility_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `facility_user_user_id_facility_id_unique` (`user_id`,`facility_id`),
  KEY `facility_user_facility_id_foreign` (`facility_id`),
  CONSTRAINT `facility_user_facility_id_foreign` FOREIGN KEY (`facility_id`) REFERENCES `facilities` (`id`) ON DELETE CASCADE,
  CONSTRAINT `facility_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `jobs`;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `main_meter_alerts`;
CREATE TABLE `main_meter_alerts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `main_meter_reading_id` bigint unsigned NOT NULL,
  `facility_id` bigint unsigned NOT NULL,
  `baseline_kwh` decimal(14,2) NOT NULL,
  `current_kwh` decimal(14,2) NOT NULL,
  `increase_percent` decimal(8,2) NOT NULL,
  `alert_level` enum('none','warning','critical') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'none',
  `reason` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `main_meter_alert_per_reading_unique` (`main_meter_reading_id`),
  KEY `main_meter_alert_facility_level_idx` (`facility_id`,`alert_level`,`created_at`),
  CONSTRAINT `main_meter_alerts_facility_id_foreign` FOREIGN KEY (`facility_id`) REFERENCES `facilities` (`id`) ON DELETE CASCADE,
  CONSTRAINT `main_meter_alerts_main_meter_reading_id_foreign` FOREIGN KEY (`main_meter_reading_id`) REFERENCES `main_meter_readings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `main_meter_baselines`;
CREATE TABLE `main_meter_baselines` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `facility_id` bigint unsigned NOT NULL,
  `baseline_type` enum('moving_avg_3','moving_avg_6','seasonal','normalized_per_day') COLLATE utf8mb4_unicode_ci NOT NULL,
  `baseline_kwh` decimal(14,2) NOT NULL,
  `baseline_kwh_per_day` decimal(14,4) NOT NULL,
  `baseline_peak_kw` decimal(12,2) DEFAULT NULL,
  `computed_for_period` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `computed_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `main_meter_baseline_unique` (`facility_id`,`baseline_type`,`computed_for_period`),
  KEY `main_meter_baseline_period_idx` (`facility_id`,`computed_for_period`),
  CONSTRAINT `main_meter_baselines_facility_id_foreign` FOREIGN KEY (`facility_id`) REFERENCES `facilities` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `main_meter_readings`;
CREATE TABLE `main_meter_readings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `facility_id` bigint unsigned NOT NULL,
  `period_type` enum('monthly') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'monthly',
  `period_start_date` date NOT NULL,
  `period_end_date` date NOT NULL,
  `reading_start_kwh` decimal(14,2) NOT NULL,
  `reading_end_kwh` decimal(14,2) NOT NULL,
  `kwh_used` decimal(14,2) GENERATED ALWAYS AS ((`reading_end_kwh` - `reading_start_kwh`)) STORED,
  `operating_days` int unsigned DEFAULT NULL,
  `peak_demand_kw` decimal(12,2) DEFAULT NULL,
  `power_factor` decimal(5,4) DEFAULT NULL,
  `encoded_by` bigint unsigned DEFAULT NULL,
  `approved_by` bigint unsigned DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `main_meter_period_unique` (`facility_id`,`period_type`,`period_start_date`,`period_end_date`),
  KEY `main_meter_readings_encoded_by_foreign` (`encoded_by`),
  KEY `main_meter_readings_approved_by_foreign` (`approved_by`),
  KEY `main_meter_facility_period_idx` (`facility_id`,`period_end_date`),
  KEY `main_meter_approval_period_idx` (`approved_at`,`period_end_date`),
  CONSTRAINT `main_meter_readings_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `main_meter_readings_encoded_by_foreign` FOREIGN KEY (`encoded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `main_meter_readings_facility_id_foreign` FOREIGN KEY (`facility_id`) REFERENCES `facilities` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `maintenance`;
CREATE TABLE `maintenance` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `facility_id` bigint unsigned NOT NULL,
  `energy_record_id` bigint unsigned DEFAULT NULL,
  `issue_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `trigger_month` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `trend` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `maintenance_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Preventive',
  `maintenance_status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Pending',
  `scheduled_date` date DEFAULT NULL,
  `assigned_to` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `completed_date` date DEFAULT NULL,
  `remarks` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `maintenance_facility_id_foreign` (`facility_id`),
  KEY `maintenance_energy_record_id_foreign` (`energy_record_id`),
  CONSTRAINT `maintenance_energy_record_id_foreign` FOREIGN KEY (`energy_record_id`) REFERENCES `energy_records` (`id`) ON DELETE SET NULL,
  CONSTRAINT `maintenance_facility_id_foreign` FOREIGN KEY (`facility_id`) REFERENCES `facilities` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `maintenance_history`;
CREATE TABLE `maintenance_history` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `facility_id` bigint unsigned NOT NULL,
  `issue_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `trigger_month` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `efficiency_rating` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `trend` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `maintenance_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Preventive',
  `maintenance_status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Completed',
  `scheduled_date` date DEFAULT NULL,
  `assigned_to` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `completed_date` date DEFAULT NULL,
  `remarks` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `maintenance_history_facility_id_foreign` (`facility_id`),
  CONSTRAINT `maintenance_history_facility_id_foreign` FOREIGN KEY (`facility_id`) REFERENCES `facilities` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=95 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_user_id_foreign` (`user_id`),
  CONSTRAINT `notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=76 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `otps`;
CREATE TABLE `otps` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `code` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `used` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `otps_user_id_foreign` (`user_id`),
  CONSTRAINT `otps_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci,
  `group` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `settings_key_unique` (`key`)
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `submeter_alerts`;
CREATE TABLE `submeter_alerts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `submeter_reading_id` bigint unsigned NOT NULL,
  `submeter_id` bigint unsigned NOT NULL,
  `baseline_value_kwh` decimal(14,2) NOT NULL,
  `current_value_kwh` decimal(14,2) NOT NULL,
  `increase_percent` decimal(8,2) NOT NULL,
  `alert_level` enum('none','warning','critical') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'none',
  `reason` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `submeter_alert_per_reading_unique` (`submeter_reading_id`),
  KEY `submeter_alert_level_idx` (`alert_level`,`created_at`),
  KEY `submeter_alert_submeter_idx` (`submeter_id`,`created_at`),
  CONSTRAINT `submeter_alerts_submeter_id_foreign` FOREIGN KEY (`submeter_id`) REFERENCES `submeters` (`id`) ON DELETE CASCADE,
  CONSTRAINT `submeter_alerts_submeter_reading_id_foreign` FOREIGN KEY (`submeter_reading_id`) REFERENCES `submeter_readings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `submeter_baselines`;
CREATE TABLE `submeter_baselines` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `submeter_id` bigint unsigned NOT NULL,
  `baseline_type` enum('moving_avg_3','moving_avg_6','seasonal_month','normalized_per_sqm') COLLATE utf8mb4_unicode_ci NOT NULL,
  `months_window` tinyint unsigned DEFAULT NULL,
  `baseline_value_kwh` decimal(14,2) NOT NULL,
  `baseline_value_normalized` decimal(14,4) DEFAULT NULL,
  `computed_for_period` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `computed_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `submeter_baseline_unique` (`submeter_id`,`baseline_type`,`computed_for_period`),
  KEY `submeter_baseline_lookup_idx` (`submeter_id`,`baseline_type`,`computed_for_period`),
  CONSTRAINT `submeter_baselines_submeter_id_foreign` FOREIGN KEY (`submeter_id`) REFERENCES `submeters` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `submeter_equipments`;
CREATE TABLE `submeter_equipments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `submeter_id` bigint unsigned NOT NULL,
  `equipment_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` int unsigned NOT NULL DEFAULT '1',
  `rated_watts` decimal(12,2) NOT NULL,
  `operating_hours_per_day` decimal(6,2) NOT NULL,
  `operating_days_per_month` smallint unsigned NOT NULL,
  `estimated_kwh` decimal(14,2) GENERATED ALWAYS AS (((((`rated_watts` * `quantity`) * `operating_hours_per_day`) * `operating_days_per_month`) / 1000)) STORED,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `submeter_equipment_unique_name` (`submeter_id`,`equipment_name`),
  KEY `submeter_equipment_lookup_idx` (`submeter_id`,`equipment_name`),
  CONSTRAINT `submeter_equipments_submeter_id_foreign` FOREIGN KEY (`submeter_id`) REFERENCES `submeters` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `submeter_readings`;
CREATE TABLE `submeter_readings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `submeter_id` bigint unsigned NOT NULL,
  `period_type` enum('daily','weekly','monthly') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'monthly',
  `period_start_date` date NOT NULL,
  `period_end_date` date NOT NULL,
  `reading_start_kwh` decimal(14,2) NOT NULL,
  `reading_end_kwh` decimal(14,2) NOT NULL,
  `kwh_used` decimal(14,2) GENERATED ALWAYS AS ((`reading_end_kwh` - `reading_start_kwh`)) STORED,
  `operating_days` int unsigned DEFAULT NULL,
  `encoded_by_user_id` bigint unsigned DEFAULT NULL,
  `approved_by_engineer_id` bigint unsigned DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `submeter_period_unique` (`submeter_id`,`period_type`,`period_start_date`,`period_end_date`),
  KEY `submeter_readings_encoded_by_user_id_foreign` (`encoded_by_user_id`),
  KEY `submeter_readings_approved_by_engineer_id_foreign` (`approved_by_engineer_id`),
  KEY `submeter_reading_period_idx` (`submeter_id`,`period_type`,`period_end_date`),
  KEY `submeter_reading_approval_idx` (`approved_at`,`period_end_date`),
  CONSTRAINT `submeter_readings_approved_by_engineer_id_foreign` FOREIGN KEY (`approved_by_engineer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `submeter_readings_encoded_by_user_id_foreign` FOREIGN KEY (`encoded_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `submeter_readings_submeter_id_foreign` FOREIGN KEY (`submeter_id`) REFERENCES `submeters` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `submeters`;
CREATE TABLE `submeters` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `facility_id` bigint unsigned NOT NULL,
  `submeter_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `meter_type` enum('single_phase','three_phase') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'single_phase',
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `submeters_facility_id_submeter_name_unique` (`facility_id`,`submeter_name`),
  KEY `submeters_facility_id_status_index` (`facility_id`,`status`),
  CONSTRAINT `submeters_facility_id_foreign` FOREIGN KEY (`facility_id`) REFERENCES `facilities` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `facility_id` bigint unsigned DEFAULT NULL,
  `full_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `profile_photo_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `username` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'user',
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `contact_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `department` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_username_unique` (`username`),
  KEY `users_facility_id_foreign` (`facility_id`),
  CONSTRAINT `users_facility_id_foreign` FOREIGN KEY (`facility_id`) REFERENCES `facilities` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS=1;
