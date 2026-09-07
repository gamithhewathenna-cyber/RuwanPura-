-- =====================================================================
-- Migration: Sub-categories for gem_categories
-- Run this once against the live database (phpMyAdmin > SQL tab).
-- Adds a self-referencing parent_id so a category can optionally be a
-- sub-category of another (top-level) category. Existing categories
-- default to NULL (top-level), so nothing changes until an admin
-- assigns a parent.
-- =====================================================================

ALTER TABLE `gem_categories`
  ADD COLUMN `parent_id` INT(11) NULL DEFAULT NULL COMMENT 'Parent category id, for sub-categories' AFTER `slug`;
