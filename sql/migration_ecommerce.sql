-- =====================================================================
-- Migration: E-Commerce & Customer Account System
-- Run this once against the live database (phpMyAdmin > SQL tab).
--
-- NOTE on re-running: the `products` ALTER TABLE statements below are
-- NOT safe to run twice on plain MySQL 5.7 (no "ADD COLUMN IF NOT EXISTS"
-- support before MySQL 8.0.29 / MariaDB 10.0.2). If your host is MariaDB
-- 10.0.2+ you can safely re-run this file as-is. If you are on MySQL
-- 5.7/8.0 (pre-8.0.29) and need to re-run it, remove the 4 ADD COLUMN
-- lines first (they will already be applied). The CREATE TABLE and
-- INSERT statements below are safe to re-run as many times as needed.
-- =====================================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
SET NAMES utf8mb4;

-- ---------------------------------------------------------------------
-- products: pricing + discount fields
-- ---------------------------------------------------------------------
ALTER TABLE `products`
  ADD COLUMN `price` DECIMAL(12,2) NULL DEFAULT NULL COMMENT 'Fixed selling price, gemstone only (excludes shipping)' AFTER `sku`,
  ADD COLUMN `discount_type` ENUM('none','amount','percentage') NOT NULL DEFAULT 'none' AFTER `price`,
  ADD COLUMN `discount_value` DECIMAL(12,2) NULL DEFAULT NULL AFTER `discount_type`,
  ADD COLUMN `discount_active` TINYINT(1) NOT NULL DEFAULT 0 AFTER `discount_value`;

-- ---------------------------------------------------------------------
-- Table: customers
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `customers` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `full_name` VARCHAR(150) NOT NULL,
  `email` VARCHAR(190) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(60) DEFAULT NULL,
  `country` VARCHAR(100) DEFAULT NULL,
  `billing_address_line1` VARCHAR(255) DEFAULT NULL,
  `billing_address_line2` VARCHAR(255) DEFAULT NULL,
  `billing_city` VARCHAR(120) DEFAULT NULL,
  `billing_state` VARCHAR(120) DEFAULT NULL,
  `billing_postal_code` VARCHAR(40) DEFAULT NULL,
  `billing_country` VARCHAR(100) DEFAULT NULL,
  `shipping_same_as_billing` TINYINT(1) NOT NULL DEFAULT 1,
  `shipping_address_line1` VARCHAR(255) DEFAULT NULL,
  `shipping_address_line2` VARCHAR(255) DEFAULT NULL,
  `shipping_city` VARCHAR(120) DEFAULT NULL,
  `shipping_state` VARCHAR(120) DEFAULT NULL,
  `shipping_postal_code` VARCHAR(40) DEFAULT NULL,
  `shipping_country` VARCHAR(100) DEFAULT NULL,
  `reset_token` VARCHAR(255) DEFAULT NULL,
  `reset_expires` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- Table: orders
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `orders` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `order_number` VARCHAR(40) NOT NULL,
  `customer_id` INT(11) NOT NULL,

  `customer_name` VARCHAR(150) NOT NULL,
  `customer_email` VARCHAR(190) NOT NULL,
  `customer_phone` VARCHAR(60) DEFAULT NULL,
  `customer_country` VARCHAR(100) DEFAULT NULL,

  `billing_address_line1` VARCHAR(255) DEFAULT NULL,
  `billing_address_line2` VARCHAR(255) DEFAULT NULL,
  `billing_city` VARCHAR(120) DEFAULT NULL,
  `billing_state` VARCHAR(120) DEFAULT NULL,
  `billing_postal_code` VARCHAR(40) DEFAULT NULL,
  `billing_country` VARCHAR(100) DEFAULT NULL,

  `shipping_address_line1` VARCHAR(255) DEFAULT NULL,
  `shipping_address_line2` VARCHAR(255) DEFAULT NULL,
  `shipping_city` VARCHAR(120) DEFAULT NULL,
  `shipping_state` VARCHAR(120) DEFAULT NULL,
  `shipping_postal_code` VARCHAR(40) DEFAULT NULL,
  `shipping_country` VARCHAR(100) DEFAULT NULL,

  `payment_method` VARCHAR(30) NOT NULL DEFAULT 'bank_transfer',

  `items_original_total` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `items_discount_total` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `gemstone_total` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `shipping_charge` DECIMAL(12,2) DEFAULT NULL,
  `final_total` DECIMAL(12,2) DEFAULT NULL,

  `order_status` ENUM(
      'order_in_process',
      'shipping_quoted',
      'payment_pending',
      'payment_verified',
      'order_confirmed',
      'shipped',
      'completed',
      'cancelled'
    ) NOT NULL DEFAULT 'order_in_process',

  `payment_reference` VARCHAR(190) DEFAULT NULL,
  `admin_notes` TEXT DEFAULT NULL,
  `shipping_confirmed_at` DATETIME DEFAULT NULL,
  `payment_confirmed_at` DATETIME DEFAULT NULL,

  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (`id`),
  UNIQUE KEY `order_number` (`order_number`),
  KEY `customer_id` (`customer_id`),
  KEY `order_status` (`order_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- Table: order_items
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `order_items` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `order_id` INT(11) NOT NULL,
  `product_id` INT(11) DEFAULT NULL,
  `product_name` VARCHAR(190) NOT NULL,
  `weight` DECIMAL(8,2) DEFAULT NULL,
  `shape` VARCHAR(120) DEFAULT NULL,
  `sku` VARCHAR(60) DEFAULT NULL,
  `image` VARCHAR(255) DEFAULT NULL,
  `quantity` INT(11) NOT NULL DEFAULT 1,
  `unit_price` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `discount_amount` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `line_total` DECIMAL(12,2) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- Bank transfer instructions (editable via the existing content-block
-- admin UI, referenced by the shipping-confirmation email)
-- ---------------------------------------------------------------------
INSERT IGNORE INTO `content_blocks` (`block_key`,`block_value`,`block_group`,`block_label`,`block_type`,`sort_order`) VALUES
('bank_name','','bank_details','Bank Name','text',1),
('bank_account_name','','bank_details','Account Holder Name','text',2),
('bank_account_number','','bank_details','Account Number','text',3),
('bank_branch','','bank_details','Branch','text',4),
('bank_swift','','bank_details','SWIFT / BIC Code','text',5),
('bank_instructions_extra','','bank_details','Additional Instructions (optional)','textarea',6);
