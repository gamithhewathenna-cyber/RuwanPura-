-- =====================================================================
-- Ruwanpura Gems - Migration: Trust badges strip (home page, below hero)
-- Run this once against your EXISTING live database.
-- =====================================================================

CREATE TABLE IF NOT EXISTS `trust_badges` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `icon` VARCHAR(255) DEFAULT NULL,
  `title` VARCHAR(150) DEFAULT NULL,
  `description` VARCHAR(400) DEFAULT NULL,
  `sort_order` INT(11) NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `trust_badges` (`icon`,`title`,`description`,`sort_order`) VALUES
('badge-shipping.svg','Worldwide Shipping','Secure delivery to customers worldwide.',1),
('badge-gemstones.svg','Authentic Gemstones','Carefully selected natural gemstones.',2),
('badge-guidance.svg','Expert Guidance','Personal assistance from our gem experts.',3),
('badge-heritage.svg','Sri Lankan Heritage','Rooted in Sri Lanka\'s gem heritage.',4),
('badge-trust.svg','Trusted & Transparent','Clear details for confident purchases.',5);
