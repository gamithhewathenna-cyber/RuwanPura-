-- =====================================================================
-- Ruwanpura Gems - Migration: Announcement Bar -> multi-card layout
-- Run this once against your EXISTING live database.
-- =====================================================================

CREATE TABLE IF NOT EXISTS `announcement_cards` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `text` VARCHAR(255) DEFAULT NULL,
  `link` VARCHAR(255) DEFAULT NULL,
  `sort_order` INT(11) NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Carry your existing single announcement message over as the first card,
-- if you had one set (safe no-op otherwise).
INSERT INTO `announcement_cards` (`text`, `link`, `sort_order`)
SELECT
    (SELECT block_value FROM content_blocks WHERE block_key = 'announcement_text'),
    NULLIF((SELECT block_value FROM content_blocks WHERE block_key = 'announcement_link'), ''),
    1
WHERE
    EXISTS (SELECT 1 FROM content_blocks WHERE block_key = 'announcement_text' AND block_value <> '')
    AND NOT EXISTS (SELECT 1 FROM announcement_cards);
