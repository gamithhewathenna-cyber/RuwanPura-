-- =====================================================================
-- Migration: Product dimensions (mm)
-- Run this once against the live database (phpMyAdmin > SQL tab).
-- =====================================================================

ALTER TABLE `products`
  ADD COLUMN `dimensions` VARCHAR(60) NULL DEFAULT NULL COMMENT 'e.g. 8.2 x 6.1 x 4.3 mm' AFTER `weight`;
