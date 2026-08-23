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
  `eyebrow` VARCHAR(190) DEFAULT NULL,
  `title` VARCHAR(255) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `btn_text` VARCHAR(100) DEFAULT NULL,
  `btn_link` VARCHAR(255) DEFAULT NULL,
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

-- ---------------------------------------------------------------------
-- Table: history_milestones  (About Us - "Our Global Journey" timeline)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `history_milestones` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `year_label` VARCHAR(60) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `sort_order` INT(11) NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- Table: achievements  (About Us - "National Industry Excellence" list)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `achievements` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(190) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `sort_order` INT(11) NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- Table: memberships  (About Us - "Professional standing, worldwide" logos)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `memberships` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(150) DEFAULT NULL,
  `description` VARCHAR(400) DEFAULT NULL,
  `logo` VARCHAR(255) DEFAULT NULL,
  `sort_order` INT(11) NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- Table: contact_messages  (Contact Us page - submitted enquiries)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `contact_messages` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `full_name` VARCHAR(150) DEFAULT NULL,
  `phone` VARCHAR(60) DEFAULT NULL,
  `email` VARCHAR(190) DEFAULT NULL,
  `company` VARCHAR(150) DEFAULT NULL,
  `message` TEXT DEFAULT NULL,
  `is_read` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- Gemstone catalogue: taxonomy tables (Category, Shape, Treatment, Origin)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `gem_categories` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(120) NOT NULL,
  `slug` VARCHAR(140) NOT NULL,
  `sort_order` INT(11) NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `gem_shapes` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(120) NOT NULL,
  `sort_order` INT(11) NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `gem_treatments` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(120) NOT NULL,
  `sort_order` INT(11) NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `gem_origins` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(120) NOT NULL,
  `sort_order` INT(11) NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- Table: products  (the gemstone catalogue)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `products` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(190) NOT NULL,
  `slug` VARCHAR(220) NOT NULL,
  `sku` VARCHAR(60) DEFAULT NULL,
  `category_id` INT(11) DEFAULT NULL,
  `shape_id` INT(11) DEFAULT NULL,
  `treatment_id` INT(11) DEFAULT NULL,
  `origin_id` INT(11) DEFAULT NULL,
  `weight` DECIMAL(8,2) DEFAULT NULL COMMENT 'Carat weight',
  `description` TEXT DEFAULT NULL,
  `certificate_info` TEXT DEFAULT NULL,
  `status` ENUM('available','reserved','sold','unavailable') NOT NULL DEFAULT 'available',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Shown on the website',
  `sort_order` INT(11) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `category_id` (`category_id`),
  KEY `shape_id` (`shape_id`),
  KEY `treatment_id` (`treatment_id`),
  KEY `origin_id` (`origin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `product_images` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `product_id` INT(11) NOT NULL,
  `image` VARCHAR(255) NOT NULL,
  `is_primary` TINYINT(1) NOT NULL DEFAULT 0,
  `sort_order` INT(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- Gemstone enquiries (cart submissions - no online payment)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `enquiries` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `full_name` VARCHAR(150) DEFAULT NULL,
  `email` VARCHAR(190) DEFAULT NULL,
  `phone` VARCHAR(60) DEFAULT NULL,
  `country` VARCHAR(100) DEFAULT NULL,
  `message` TEXT DEFAULT NULL,
  `is_read` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `enquiry_items` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `enquiry_id` INT(11) NOT NULL,
  `product_id` INT(11) DEFAULT NULL,
  `product_name` VARCHAR(190) DEFAULT NULL,
  `weight` DECIMAL(8,2) DEFAULT NULL,
  `shape` VARCHAR(120) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `enquiry_id` (`enquiry_id`)
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
('site_logo_white',''),
('theme_primary','#c99a5b'),
('theme_dark','#0d0d0d'),
('admin_email','gamithhewathenna@gmail.com'),
('site_name','Ruwanpura Gems'),
('maintenance_mode','0'),
('maintenance_message','We\'re currently performing scheduled maintenance. Please check back soon.'),
('noindex_site','1');

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
('footer_copyright','Ruwanpura Gems @2026, All Right reserved by Creativelements','footer','Footer Copyright','text',10),
-- About: Hero band
('about_hero_eyebrow','EST. 1985','about_hero','Hero Eyebrow','text',1),
('about_hero_title','Ruwanpura Gems','about_hero','Hero Title','text',2),
('about_hero_quote','From a foundation of gold to a world of color— preserving a legacy of meticulous artistry.','about_hero','Hero Quote','textarea',3),
-- About: The Radical Evolution
('evolution_title','The Radical Evolution','about_evolution','Title','text',1),
('evolution_p1','In the early 1990s, the jewellery landscape in Sri Lanka was defined by traditional, plain gold assortments—a standard our founders felt compelled to challenge.','about_evolution','Paragraph 1','textarea',2),
('evolution_p2','Driven by a rare sense of creativity and a vision for the future, they made the radical decision to pivot from a pure gold foundation into the vibrant world of colored gemstones. This "new move" was initially met with significant reluctance from traditional craftsmen and employees who were hesitant to abandon deviously ancient methods for an unproven path.','about_evolution','Paragraph 2','textarea',3),
('evolution_p3','Despite the inherent risks and the flurry of industry skepticism, our founders held firmly to their instincts. This pivotal "icebreaker" moment effectively transformed our business, merging the meticulous discipline of gold craftsmanship with the whirlwind of beauty and artistry found in precious stones. Today, that same obsession for perfection defines the very soul of Ruwanpura Gems.','about_evolution','Paragraph 3','textarea',4),
('evolution_image','','about_evolution','Main Photo','image',5),
('evolution_badge_image','','about_evolution','Signage / Badge Graphic (optional overlay)','image',6),
-- About: Our Global Journey (timeline heading)
('history_eyebrow','OUR GLOBAL JOURNEY','history','Eyebrow','text',1),
('history_title','Our Global Journey','history','Title','text',2),
-- About: Direct From The Source (video + responsibility)
('video_eyebrow','DIRECT FROM THE SOURCE','about_video','Eyebrow','text',1),
('video_thumbnail','','about_video','Video Thumbnail Image','image',2),
('video_url','','about_video','Video URL (YouTube / Vimeo / mp4)','link',3),
('video_heading','Social & environmental responsibility','about_video','Heading','text',4),
('video_p1','At Ruwanpura Gems, we distinguish ourselves from average retailers by maintaining a rigorous and complete chain of custody for every gemstone in our collection. Since our strategic decision in 2012 to invest directly in mining operations across Madagascar and Mozambique, we have secured the ability to oversee the journey of a gemstone from the moment it leaves the earth until it reaches your hands. This "mine-to-market" approach allows us to guarantee that every stone is sourced with the highest level of integrity and transparency.','about_video','Paragraph 1','textarea',5),
('video_p2','Our dedication to excellence extends beyond the quality of the gems to the wellbeing of the people and environmental standards. Our carefully selected mining teams operate under strict labor and environmental requirements, ensuring a safe and fair workplace for all involved. By upholding these socially and environmentally responsible practices, we ensure that every stone supplied to our wholesale buyers, jewelry manufacturers, and collectors is ethically produced and of the highest artisanal standard.','about_video','Paragraph 2','textarea',6),
('video_card1_title','Scientific integrity','about_video','Card 1 Title','text',7),
('video_card1_desc','Every gem undergoes a rigorous vetting process in our labs to meet the high standards expected by our global clientele. We ensure our stones meet all certification requirements necessary for international trade.','about_video','Card 1 Description','textarea',8),
('video_card2_title','Global distribution','about_video','Card 2 Title','text',9),
('video_card2_desc','From our roots in Sri Lanka to our operations in Bangkok, Hong Kong, and Chicago, we have established a seamless network providing peace of mind and top-quality products to the world.','about_video','Card 2 Description','textarea',10),
-- About: National Industry Excellence
('awards_eyebrow','PRESTIGIOUS RECOGNITION','awards','Eyebrow','text',1),
('awards_title','National Industry Excellence','awards','Title','text',2),
('awards_image','','awards','Trophy Photo','image',3),
-- About: Gubelin Gem Lab
('gubelin_eyebrow','PRESTIGIOUS RECOGNITION','gubelin','Eyebrow','text',1),
('gubelin_title','Gübelin Gem Lab','gubelin','Title','text',2),
('gubelin_subtitle','Token of Appreciation for Industry Contribution','gubelin','Subtitle','text',3),
('gubelin_desc','Issued by the globally distinguished Gübelin Gem Lab across its core international hubs in Lucerne, Hong Kong, and New York. This unique honor celebrates critical contributions toward authoritative gemmological research activities. This institutional recognition underscores a shared commitment to expanding systemic geological data, preserving market transparency, and anchoring absolute public trust within the global gemstone industry.','gubelin','Description','textarea',4),
('gubelin_sign1_name','Dr. Daniel Nyfeler','gubelin','Signature 1 Name','text',5),
('gubelin_sign1_title','Managing Director','gubelin','Signature 1 Title','text',6),
('gubelin_sign2_name','Raphael Gübelin','gubelin','Signature 2 Name','text',7),
('gubelin_sign2_title','President','gubelin','Signature 2 Title','text',8),
('gubelin_image','','gubelin','Certificate Photo','image',9),
-- About: Professional standing, worldwide (memberships)
('membership_eyebrow','MEMBERSHIPS','membership','Eyebrow','text',1),
('membership_title','Professional standing, worldwide','membership','Title','text',2),
('membership_p1','Ruwanpura Gems is defined by a commitment to the highest tiers of professional integrity and ethical trade. Our global standing is reinforced through active membership in the world\'s most respected jewelry and trade organizations.','membership','Paragraph 1','textarea',3),
('membership_p2','By aligning with these prestigious institutions, Ruwanpura Gems ensures a complete "Mine-to-Market" chain of custody that is backed by global certification and recognized authority. Our clients—ranging from wholesale buyers to private collectors—can acquire every stone with absolute confidence in its quality and provenance.','membership','Paragraph 2','textarea',4),
-- Contact: Hero band
('contact_hero_eyebrow','EST. 1985','contact_hero','Hero Eyebrow','text',1),
('contact_hero_title','Contact Us','contact_hero','Hero Title','text',2),
('contact_hero_quote','Lorem ipsum dolor sit amet consectetur. Id adipiscing lacinia pretium duis lorem justo.','contact_hero','Hero Quote','textarea',3),
-- Contact: Info + form
('contact_heading','Let\'s Find Your Perfect Gem','contact','Heading','text',1),
('contact_p1','Whether you\'re searching for a rare coloured gemstone, sourcing stones for a jewellery collection, or simply want expert advice, we\'d love to hear from you.','contact','Paragraph 1','textarea',2),
('contact_p2','Have a gemstone in mind? Tell us what you\'re looking for and our team will be happy to assist you.','contact','Paragraph 2','textarea',3),
('contact_form_intro','Comments, questions, or looking for something special? Drop us a note, and our gemstone experts will be happy to assist you.','contact','Form Intro Text','textarea',4),
('contact_map_embed','','contact','Map Embed URL (Google Maps "Embed a map" src)','link',5);

-- Hero slides
INSERT INTO `hero_slides` (`image`,`eyebrow`,`title`,`description`,`btn_text`,`btn_link`,`sort_order`) VALUES
('','SRI LANKA · EST. 1985','World\'s Most Extraordinary Gemstones','Discover the finest sapphires on Earth — gems celebrated for their breathtaking colors, impressive sizes, and timeless allure.','Discover More','#collection',1),
('',NULL,NULL,NULL,NULL,NULL,2),
('',NULL,NULL,NULL,NULL,NULL,3);

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

-- History milestones (About Us - Our Global Journey)
INSERT INTO `history_milestones` (`year_label`,`description`,`sort_order`) VALUES
('1985','Founded as Ruwanpura Gold House and Ratnapura Gold House, specializing in 22KT pure gold jewelry.',1),
('1999','Official formation of Ruwanpura Gems; began exporting gems abroad at a rapid pace. Established Sri Lankan procurement office in Ratnapura.',2),
('Early 2000s - 2011','Expanded with international sales office in Silom, Bangkok to serve Asia-pacific region.',3),
('2012','Invested in strategic mining operations in Madagascar and Mozambique to ensure a direct source of quality.',4),
('2014','Established our North American office in the USA to serve American continents and European region.',5),
('2022','Opening of our "Ruwanpura Facets" cutting factory in Kuruvita, Sri Lanka. Retail operations began at JTC (Jewelry Trade center) in Silom, Bangkok.',6),
('2026','Establishing "RuwanpuraBLU", a lapidary and a cutting facility for fine Blue Sapphire calibrated gemstones in Ratnapura, Sri Lanka.',7);

-- Achievements (About Us - National Industry Excellence)
INSERT INTO `achievements` (`title`,`description`,`sort_order`) VALUES
('Gold Award Distinction','Highest honor received at the National Industry Excellence Awards 2023.',1),
('Government Recognition','Awarded by the Industrial Development Board under the Ministry of Industries.',2),
('Sector Leadership','Recognized as a leader in the Gem Polishing and Cutting Sector (Medium Scale).',3),
('Outstanding Dedication','Honored for vital dedication and outstanding input within the gemstone industry.',4),
('National Benchmark','Reinforcing Ruwanpura Gems\' status as a prominent advocate for the Sri Lankan gem industry.',5);

-- Memberships (About Us - Professional standing, worldwide)
INSERT INTO `memberships` (`name`,`description`,`sort_order`) VALUES
('International Colored Gemstone Association (ICA)','Our membership signifies for both our commitment to ethical and international standards of conduct in the colored gemstone trade.',1),
('American Gem Society (AGS)','This affiliation highlights our dedication to consumer protection, ethical business practices, and superior gemological knowledge.',2),
('Jewelers of America (JA)','Membership in JA connects us with a national community committed to shared ethical standards in the jewelry industry.',3),
('Jewelers Board of Trade (JBT)','This connection integrates us into the premier credit information and business intelligence network for the jewelry trade.',4),
('American Chamber of Commerce in Sri Lanka','As a close trading partner, this membership highlights our role in fostering strong commercial ties between Sri Lanka and the American continents.',5);

-- Gemstone catalogue taxonomy defaults
INSERT INTO `gem_categories` (`name`,`slug`,`sort_order`) VALUES
('Blue Sapphire','blue-sapphire',1),
('Padparadscha Sapphire','padparadscha-sapphire',2),
('Yellow & Orange Sapphire','yellow-orange-sapphire',3),
('Alexandrite','alexandrite',4),
('Ruby','ruby',5),
('Spinel','spinel',6);

INSERT INTO `gem_shapes` (`name`,`sort_order`) VALUES
('Oval',1),('Cushion',2),('Round',3),('Pear',4),('Emerald Cut',5),('Cabochon',6);

INSERT INTO `gem_treatments` (`name`,`sort_order`) VALUES
('Unheated',1),('Heated',2),('Diffusion',3);

INSERT INTO `gem_origins` (`name`,`sort_order`) VALUES
('Sri Lanka',1),('Madagascar',2),('Mozambique',3),('Thailand',4);
