-- =====================================================================
-- Ruwanpura Gems - Migration: Hero video background (replaces the slider)
-- Run this once against your EXISTING live database.
-- Safe to re-run.
-- =====================================================================

INSERT INTO `content_blocks` (`block_key`,`block_value`,`block_group`,`block_label`,`block_type`,`sort_order`) VALUES
('hero_video','','hero','Hero Video (MP4, plays muted/looped in the background)','video',6),
('hero_video_poster','','hero','Hero Poster Image (shown before the video loads, and as a fallback)','image',7)
ON DUPLICATE KEY UPDATE `block_value` = `block_value`;
