-- =====================================================================
-- Ruwanpura Gems - Database Schema (Phase 1)
-- Home Page CMS + Admin Backend
-- Compatible with MySQL 5.7+ / MariaDB (cPanel)
-- =====================================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

-- ---------------------------------------------------------------------
-- Table: admins
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `admins` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(120) NOT NULL,
  `email` VARCHAR(190) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `reset_token` VARCHAR(255) DEFAULT NULL,
  `reset_expires` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- Table: settings  (global site settings - logo, colours, etc.)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `settings` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `setting_key` VARCHAR(100) NOT NULL,
  `setting_value` TEXT DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- Table: content_blocks  (key/value store for editable text + images
--         belonging to fixed single-instance sections)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `content_blocks` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `block_key` VARCHAR(120) NOT NULL,
  `block_value` TEXT DEFAULT NULL,
  `block_group` VARCHAR(80) NOT NULL DEFAULT 'general',
  `block_label` VARCHAR(190) DEFAULT NULL,
  `block_type` VARCHAR(20) NOT NULL DEFAULT 'text', -- text | textarea | image | link
  `sort_order` INT(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `block_key` (`block_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- Table: hero_slides  (hero image carousel)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `hero_slides` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `image` VARCHAR(255) DEFAULT NULL,
  `sort_order` INT(11) NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- Table: gemstones  ("Explore Our Gemstones" carousel)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `gemstones` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(150) NOT NULL,
  `image` VARCHAR(255) DEFAULT NULL,
  `sort_order` INT(11) NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- Table: branches  ("Our Branches" list)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `branches` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(190) NOT NULL,
  `description` VARCHAR(400) DEFAULT NULL,
  `sort_order` INT(11) NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- Table: partners  (Exhibitions & Trade Shows logos)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `partners` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(150) DEFAULT NULL,
  `image` VARCHAR(255) DEFAULT NULL,
  `sort_order` INT(11) NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- Table: testimonials  ("What Our Clients Say")
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `testimonials` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `quote` TEXT DEFAULT NULL,
  `author_name` VARCHAR(150) DEFAULT NULL,
  `author_role` VARCHAR(190) DEFAULT NULL,
  `avatar` VARCHAR(255) DEFAULT NULL,
  `sort_order` INT(11) NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================================
-- DEFAULT DATA
-- =====================================================================

-- Default admin  (email: gamithhewathenna@gmail.com  password: Admin@123)
INSERT INTO `admins` (`name`,`email`,`password_hash`) VALUES
('Administrator','gamithhewathenna@gmail.com','$2y$10$8mjLQS8QYzL5xBRq83g6D.KTG15zLZHuy8WdXT9BtGSOMQXfiEVA6');

-- Settings
INSERT INTO `settings` (`setting_key`,`setting_value`) VALUES
('site_logo',''),
('theme_primary','#c99a5b'),
('theme_dark','#0d0d0d'),
('admin_email','gamithhewathenna@gmail.com'),
('site_name','Ruwanpura Gems'),
('maintenance_mode','0'),
('maintenance_message','We\'re currently performing scheduled maintenance. Please check back soon.');

-- Content blocks -------------------------------------------------------
INSERT INTO `content_blocks` (`block_key`,`block_value`,`block_group`,`block_label`,`block_type`,`sort_order`) VALUES
-- Header / nav
('nav_home','Home','header','Nav: Home Label','text',1),
('nav_gemstones','Our Gemstones','header','Nav: Gemstones Label','text',2),
('nav_about','About us','header','Nav: About Label','text',3),
('nav_news','News & Blogs','header','Nav: News Label','text',4),
('nav_contact','Contact Us','header','Nav: Contact Label','text',5),
-- Hero
('hero_eyebrow','SRI LANKA · EST. 1985','hero','Hero Eyebrow','text',1),
('hero_title','World\'s Most Extraordinary Gemstones','hero','Hero Title','textarea',2),
('hero_desc','Discover the finest sapphires on Earth — gems celebrated for their breathtaking colors, impressive sizes, and timeless allure.','hero','Hero Description','textarea',3),
('hero_btn_text','Discover More','hero','Hero Button Text','text',4),
('hero_btn_link','#collection','hero','Hero Button Link','link',5),
-- Journey
('journey_eyebrow','REFINED GROWTH','journey','Journey Eyebrow','text',1),
('journey_title','A Journey Defined by Vision & Trust','journey','Journey Title','textarea',2),
('journey_p1','Ruwanpura Gems\' journey has been defined by vision, momentum, and global ambition. What began as steady growth quickly evolved into an international expansion strategy rooted in excellence and trust.','journey','Journey Paragraph 1','textarea',3),
('journey_p2','By late 2010, we had established two key Sri Lankan branches in Colombo and Ratnapura — the heart of the world\'s finest sapphires.','journey','Journey Paragraph 2','textarea',4),
('journey_image','','journey','Journey Image','image',5),
-- Collection
('collection_eyebrow','OUR COLLECTION','collection','Collection Eyebrow','text',1),
('collection_title','Explore Our Gemstones','collection','Collection Title','text',2),
-- Factory
('factory_eyebrow','PRODUCTION','factory','Factory Eyebrow','text',1),
('factory_title','Factory & Laboratories','factory','Factory Title','textarea',2),
('factory_p1','Ruwanpura Gems operates a meticulous production pipeline, transitioning from its 1985 "Gold House" foundations to a global gemstone powerhouse. All cutting, polishing, and assortment are managed by their specialized subsidiaries, Ruwanpura Facets and RuwanpuraBlu, where highly vetted craftsmen utilize traditional artistry alongside modern precision.','factory','Factory Paragraph 1','textarea',3),
('factory_p2','The company maintains a strict chain of custody through its own mining operations in Sri Lanka and investments in Madagascar and Mozambique. Their laboratories ensure every stone is ethically sourced, following rigorous labor and environmental standards. This commitment to quality and transparency provides customers with an authentic, sustainably produced gemstone experience.','factory','Factory Paragraph 2','textarea',4),
('factory_image1','','factory','Factory Image 1','image',5),
('factory_image2','','factory','Factory Image 2','image',6),
('factory_image3','','factory','Factory Image 3','image',7),
-- Branches
('branches_eyebrow','GLOBAL PRESENCE','branches','Branches Eyebrow','text',1),
('branches_title','Our Branches','branches','Branches Title','text',2),
('branches_desc','Ruwanpura Gems has strategically expanded from its Sri Lankan roots to establish a significant international presence across key gem hubs worldwide.','branches','Branches Description','textarea',3),
('branches_map','','branches','Branches Map Image','image',4),
-- Events
('events_eyebrow','EVENTS','events','Events Eyebrow','text',1),
('events_title','Exhibitions & Trade Shows','events','Events Title','text',2),
('events_desc','Participating in these shows and exhibitions has given our brand a good exposure and meet some of our great clients.','events','Events Description','textarea',3),
-- Testimonials
('testi_eyebrow','CLIENT EXPERIENCES','testimonials','Testimonials Eyebrow','text',1),
('testi_title','What Our Clients Say','testimonials','Testimonials Title','text',2),
('testi_desc','Trusted by gemstone enthusiasts, jewellery professionals, and collectors who value quality, authenticity, and exceptional service.','testimonials','Testimonials Description','textarea',3),
-- CTA
('cta_title','Discover the Beauty of Fine Gems','cta','CTA Title','textarea',1),
('cta_desc','Explore our carefully selected collection of exceptional gemstones, sourced with integrity and crafted for those who value quality.','cta','CTA Description','textarea',2),
('cta_box_text','NEED HELP CHOOSING THE RIGHT GEM?','cta','CTA Box Text','textarea',3),
('cta_btn_text','Contact US','cta','CTA Button Text','text',4),
('cta_btn_link','#contact','cta','CTA Button Link','link',5),
('cta_image','','cta','CTA Background Image','image',6),
-- Footer
('footer_about','Lorem ipsum dolor sit amet consectetur. Pharetra at pretium fringilla nisl feugiat. Purus vel lectus faucibus non porttitor sit magna tincidunt tellus. Ut odio in vitae mollis tortor ultrices.','footer','Footer About Text','textarea',1),
('footer_address','State tower 1055/506, Si Lom, Bang Rak, Bangkok 10500, Thailand','footer','Footer Address','textarea',2),
('footer_email','info@ruwanpuragems.com','footer','Footer Email','text',3),
('footer_phone1','+66 92 686 2666','footer','Footer Phone 1','text',4),
('footer_phone2','+1 (646) 285-7168','footer','Footer Phone 2','text',5),
('footer_facebook','#','footer','Facebook Link','link',6),
('footer_instagram','#','footer','Instagram Link','link',7),
('footer_tiktok','#','footer','Tiktok Link','link',8),
('footer_twitter','#','footer','Twitter Link','link',9),
('footer_copyright','Ruwanpura Gems @2026, All Right reserved by Creativelements','footer','Footer Copyright','text',10);

-- Hero slides
INSERT INTO `hero_slides` (`image`,`sort_order`) VALUES ('',1),('',2),('',3);

-- Gemstones
INSERT INTO `gemstones` (`name`,`sort_order`) VALUES
('Alexandrite',1),
('Padparadscha (Pasparaja)',2),
('Blue Sapphire',3),
('Yellow & Orange Sapphires',4);

-- Branches
INSERT INTO `branches` (`title`,`description`,`sort_order`) VALUES
('Ratnapura & Colombo, Sri Lanka','Headquarters · Est. 1985 · Source of the world\'s finest sapphires',1),
('Bangkok, Thailand','Branch Office · Early 2000s · JTC Jewelry Trade Center, Silom (Retail, 2022)',2),
('Chicago & New York, USA','North American Branch · Est. 2014 · Diamond District, Manhattan',3);

-- Partners
INSERT INTO `partners` (`name`,`sort_order`) VALUES
('Show 1',1),('JCK',2),('Show 3',3),('Show 4',4),('Show 5',5),('GEM Sri Lanka',6),('Facets',7);

-- Testimonials
INSERT INTO `testimonials` (`quote`,`author_name`,`author_role`,`sort_order`) VALUES
('Lorem ipsum dolor sit amet consectetur. At lobortis volutpat pharetra leo mi. Proin aliquam ullamcorper senectus arcu ornare. Amet quam ante faucibus a.','LOREM IPSUM','Lorem ipsum dolor sit amet',1),
('Lorem ipsum dolor sit amet consectetur. At lobortis volutpat pharetra leo mi. Proin aliquam ullamcorper senectus arcu ornare. Amet quam ante faucibus a.','LOREM IPSUM','Lorem ipsum dolor sit amet',2),
('Lorem ipsum dolor sit amet consectetur. At lobortis volutpat pharetra leo mi. Proin aliquam ullamcorper senectus arcu ornare. Amet quam ante faucibus a.','LOREM IPSUM','Lorem ipsum dolor sit amet',3),
('Lorem ipsum dolor sit amet consectetur. At lobortis volutpat pharetra leo mi. Proin aliquam ullamcorper senectus arcu ornare. Amet quam ante faucibus a.','LOREM IPSUM','Lorem ipsum dolor sit amet',4);
