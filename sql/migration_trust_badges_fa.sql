-- =====================================================================
-- Ruwanpura Gems - Migration: switch Trust Badges icons to Font Awesome
-- Run this once against your EXISTING live database.
-- Safe to re-run.
-- =====================================================================

UPDATE `trust_badges` SET `icon` = 'fa-solid fa-globe'          WHERE `icon` = 'badge-shipping.svg';
UPDATE `trust_badges` SET `icon` = 'fa-solid fa-gem'            WHERE `icon` = 'badge-gemstones.svg';
UPDATE `trust_badges` SET `icon` = 'fa-solid fa-headset'        WHERE `icon` = 'badge-guidance.svg';
UPDATE `trust_badges` SET `icon` = 'fa-solid fa-location-dot'   WHERE `icon` = 'badge-heritage.svg';
UPDATE `trust_badges` SET `icon` = 'fa-solid fa-shield-halved'  WHERE `icon` = 'badge-trust.svg';
