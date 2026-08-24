-- =====================================================================
-- Ruwanpura Gems - Migration: "Why Choose Ruwanpura Gems" home section
-- Run this once against your EXISTING live database.
-- Safe to re-run.
-- =====================================================================

SET NAMES utf8mb4;

INSERT INTO `content_blocks` (`block_key`,`block_value`,`block_group`,`block_label`,`block_type`,`sort_order`) VALUES
('why_eyebrow','WHY CHOOSE RUWANPURA GEMS','why_choose','Eyebrow','text',1),
('why_title','A Legacy of Trust. A World of Exceptional Gemstones.','why_choose','Title','textarea',2),
('why_desc','For over four decades, Ruwanpura Gems has built its reputation on expertise, authenticity, responsible sourcing, and a deep understanding of the world\'s finest gemstones.','why_choose','Description','textarea',3),
('why_image','','why_choose','Image','image',4),
('why_item1_title','40+ Years of Expertise','why_choose','Item 1 Title','text',5),
('why_item1_desc','Decades of experience in sourcing, evaluating, cutting, and supplying exceptional gemstones.','why_choose','Item 1 Description','textarea',6),
('why_item2_title','Natural Gemstones','why_choose','Item 2 Title','text',7),
('why_item2_desc','A carefully selected collection of natural gemstones chosen for their beauty, character, and quality.','why_choose','Item 2 Description','textarea',8),
('why_item3_title','Ethical Sourcing','why_choose','Item 3 Title','text',9),
('why_item3_desc','A responsible approach to sourcing, built around trusted relationships, transparency, and integrity.','why_choose','Item 3 Description','textarea',10),
('why_item4_title','Global Presence','why_choose','Item 4 Title','text',11),
('why_item4_desc','Serving gemstone professionals, jewellers, collectors, and clients across international markets.','why_choose','Item 4 Description','textarea',12)
ON DUPLICATE KEY UPDATE `block_value` = `block_value`;
