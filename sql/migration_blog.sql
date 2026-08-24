-- =====================================================================
-- Ruwanpura Gems - Migration: Gemstone Insights (Blog)
-- Run this once against your EXISTING live database (e.g. via phpMyAdmin).
-- Safe to re-run: uses IF NOT EXISTS / ON DUPLICATE KEY.
-- =====================================================================

SET NAMES utf8mb4;

-- ---------------------------------------------------------------------
-- Table: blog_categories
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `blog_categories` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(120) NOT NULL,
  `slug` VARCHAR(140) NOT NULL,
  `sort_order` INT(11) NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- Table: blog_posts
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `blog_posts` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(220) NOT NULL,
  `slug` VARCHAR(240) NOT NULL,
  `category_id` INT(11) DEFAULT NULL,
  `excerpt` VARCHAR(400) DEFAULT NULL,
  `content` TEXT DEFAULT NULL,
  `featured_image` VARCHAR(255) DEFAULT NULL,
  `author_name` VARCHAR(150) DEFAULT NULL,
  `status` ENUM('draft','published') NOT NULL DEFAULT 'draft',
  `published_at` DATETIME DEFAULT NULL,
  `seo_title` VARCHAR(220) DEFAULT NULL,
  `seo_description` VARCHAR(400) DEFAULT NULL,
  `seo_keyphrase` VARCHAR(190) DEFAULT NULL,
  `canonical_url` VARCHAR(255) DEFAULT NULL,
  `og_title` VARCHAR(220) DEFAULT NULL,
  `og_description` VARCHAR(400) DEFAULT NULL,
  `og_image` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `category_id` (`category_id`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- Default categories (safe to edit/delete afterwards from the admin)
-- ---------------------------------------------------------------------
INSERT INTO `blog_categories` (`name`, `slug`, `sort_order`) VALUES
('Gemstone Education', 'gemstone-education', 1),
('Buying Guides', 'buying-guides', 2),
('Company News', 'company-news', 3)
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- ---------------------------------------------------------------------
-- Rename the "News & Blogs" nav label to "Insights"
-- ---------------------------------------------------------------------
UPDATE `content_blocks` SET `block_value` = 'Insights' WHERE `block_key` = 'nav_news';

-- ---------------------------------------------------------------------
-- Hero band content blocks for the new blog.php listing page
-- (editable afterwards from Admin -> Insights -> Hero Band)
-- ---------------------------------------------------------------------
INSERT INTO `content_blocks` (`block_key`,`block_value`,`block_group`,`block_label`,`block_type`,`sort_order`) VALUES
('blog_hero_eyebrow','INSIGHTS & STORIES','blog_hero','Hero Eyebrow','text',1),
('blog_hero_title','Insights','blog_hero','Hero Title','text',2),
('blog_hero_desc','Guides, stories, and news from the world of fine coloured gemstones — written by the Ruwanpura Gems team.','blog_hero','Hero Description','textarea',3)
ON DUPLICATE KEY UPDATE `block_value` = `block_value`;
