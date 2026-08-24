-- =====================================================================
-- Ruwanpura Gems - Migration: "Our Legacy in Numbers" (home page, below hero)
-- Run this once against your EXISTING live database.
-- =====================================================================

CREATE TABLE IF NOT EXISTS `legacy_stats` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `icon` VARCHAR(255) DEFAULT NULL,
  `stat_value` VARCHAR(60) DEFAULT NULL,
  `stat_label` VARCHAR(150) DEFAULT NULL,
  `description` VARCHAR(400) DEFAULT NULL,
  `sort_order` INT(11) NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `legacy_stats` (`icon`,`stat_value`,`stat_label`,`description`,`sort_order`) VALUES
('legacy-experience.svg','40+ Years','Gemstone Expertise','Four decades of knowledge, craftsmanship, and trust.',1),
('legacy-continents.svg','3 Continents','Global Presence','Connecting Sri Lanka\'s gemstones with the world.',2),
('legacy-natural.svg','100% Natural','Exceptional Gemstones','Carefully selected for quality, beauty, and character.',3),
('legacy-network.svg','Global Network','Worldwide Reach','Serving collectors, jewellers, and gemstone professionals worldwide.',4);

INSERT INTO `content_blocks` (`block_key`,`block_value`,`block_group`,`block_label`,`block_type`,`sort_order`) VALUES
('legacy_title','Our Legacy in Numbers','legacy','Section Title','text',1)
ON DUPLICATE KEY UPDATE `block_value` = `block_value`;
