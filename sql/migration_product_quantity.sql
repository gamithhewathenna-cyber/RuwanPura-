-- =====================================================================
-- Migration: Product stock quantity
-- Run this once against the live database (phpMyAdmin > SQL tab).
-- Adds a stock-count column to products. Existing gemstones (previously
-- always treated as one-of-a-kind) default to quantity = 1, so nothing
-- about their current behaviour changes until an admin edits the value.
-- =====================================================================

ALTER TABLE `products`
  ADD COLUMN `quantity` INT(11) NOT NULL DEFAULT 1 COMMENT 'Units in stock' AFTER `discount_active`;
