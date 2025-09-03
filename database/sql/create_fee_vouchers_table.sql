-- MySQL CREATE TABLE statement for fee_vouchers table
-- Generated from Laravel migration: 2025_08_29_000001_create_fee_vouchers_table.php

CREATE TABLE `fee_vouchers` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `voucher_number` VARCHAR(255) NOT NULL UNIQUE,
    `student_id` INT NULL,
    `student_name` VARCHAR(255) NOT NULL,
    `admission_number` VARCHAR(255) NOT NULL,
    `parent_name` VARCHAR(255) NOT NULL,
    `parent_phone` VARCHAR(255) NULL,
    `parent_email` VARCHAR(255) NULL,
    `class_name` VARCHAR(255) NOT NULL,
    `fee_amount` DECIMAL(10, 2) NOT NULL,
    `fine_amount` DECIMAL(10, 2) NOT NULL DEFAULT 0,
    `total_with_fine` DECIMAL(10, 2) NOT NULL,
    `due_date` DATE NOT NULL,
    `voucher_type` ENUM('monthly', 'custom', 'multiple') NOT NULL DEFAULT 'monthly',
    `custom_amount` DECIMAL(10, 2) NULL,
    `fee_month` VARCHAR(255) NULL COMMENT 'Format: YYYY-MM',
    `fee_breakdown` JSON NULL COMMENT 'Store multiple fee types breakdown',
    `selected_fee_types` JSON NULL COMMENT 'Store selected fee type IDs',
    `notes` TEXT NULL,
    `status` ENUM('paid', 'unpaid', 'cancelled') NOT NULL DEFAULT 'unpaid',
    `paid_amount` DECIMAL(10, 2) NULL,
    `payment_date` DATE NULL,
    `last_reminder_sent` TIMESTAMP NULL,
    `generated_by` INT NULL,
    `generated_at` TIMESTAMP NULL,
    `updated_by` INT NULL,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    `deleted_at` TIMESTAMP NULL,
    
    PRIMARY KEY (`id`),
    
    -- Indexes for better performance
    INDEX `idx_student_status` (`student_id`, `status`),
    INDEX `idx_due_date_status` (`due_date`, `status`),
    INDEX `idx_voucher_number` (`voucher_number`),
    INDEX `idx_status` (`status`),
    INDEX `idx_generated_at` (`generated_at`),
    INDEX `idx_fee_month` (`fee_month`)
    
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
