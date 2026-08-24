-- =====================================================================
-- Ruwanpura Gems - Migration: Product video (MP4) in the gallery
-- Run this once against your EXISTING live database.
-- =====================================================================

ALTER TABLE `products` ADD COLUMN `video` VARCHAR(255) DEFAULT NULL AFTER `certificate_info`;
