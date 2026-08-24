-- =====================================================================
-- Ruwanpura Gems - Migration: Announcement Bar
-- Run this once against your EXISTING live database.
-- Safe to re-run.
-- =====================================================================

SET NAMES utf8mb4;

INSERT INTO `content_blocks` (`block_key`,`block_value`,`block_group`,`block_label`,`block_type`,`sort_order`) VALUES
('announcement_text','','announcement','Announcement Text (leave blank to hide the bar)','textarea',1),
('announcement_link','','announcement','Announcement Link (optional)','link',2)
ON DUPLICATE KEY UPDATE `block_value` = `block_value`;
