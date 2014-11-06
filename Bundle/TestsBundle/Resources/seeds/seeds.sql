# ************************************************************
# Sequel Pro SQL dump
# Version 4096
#
# http://www.sequelpro.com/
# http://code.google.com/p/sequel-pro/
#
# Hôte: localhost (MySQL 5.5.25)
# Base de données: testposition
# Temps de génération: 2014-11-03 11:19:53 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Affichage de la table vic_article_tags
# ------------------------------------------------------------

DROP TABLE IF EXISTS `vic_article_tags`;

CREATE TABLE `vic_article_tags` (
  `article_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL,
  PRIMARY KEY (`article_id`,`tag_id`),
  KEY `IDX_919694F97294869C` (`article_id`),
  KEY `IDX_919694F9BAD26311` (`tag_id`),
  CONSTRAINT `FK_2E6D5EDA7294869C` FOREIGN KEY (`article_id`) REFERENCES `vic_view` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_919694F9BAD26311` FOREIGN KEY (`tag_id`) REFERENCES `vic_tag` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



# Affichage de la table vic_category
# ------------------------------------------------------------

DROP TABLE IF EXISTS `vic_category`;

CREATE TABLE `vic_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



# Affichage de la table vic_entity_proxy
# ------------------------------------------------------------

DROP TABLE IF EXISTS `vic_entity_proxy`;

CREATE TABLE `vic_entity_proxy` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `article_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_2E15B1BA7294869C` (`article_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



# Affichage de la table vic_media
# ------------------------------------------------------------

DROP TABLE IF EXISTS `vic_media`;

CREATE TABLE `vic_media` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `folder_id` bigint(20) DEFAULT NULL,
  `uuid` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `location` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `content_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `metadata` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:array)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `filesize` int(11) DEFAULT NULL,
  `url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_419CAF2AD17F50A6` (`uuid`),
  KEY `IDX_419CAF2A162CB942` (`folder_id`),
  CONSTRAINT `FK_419CAF2A162CB942` FOREIGN KEY (`folder_id`) REFERENCES `vic_media_folders` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



# Affichage de la table vic_media_folders
# ------------------------------------------------------------

DROP TABLE IF EXISTS `vic_media_folders`;

CREATE TABLE `vic_media_folders` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `parent_id` bigint(20) DEFAULT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `rel` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `internal_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_5C1D48AA727ACA70` (`parent_id`),
  CONSTRAINT `FK_5C1D48AA727ACA70` FOREIGN KEY (`parent_id`) REFERENCES `vic_media_folders` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



# Affichage de la table vic_page_seo
# ------------------------------------------------------------

DROP TABLE IF EXISTS `vic_page_seo`;

CREATE TABLE `vic_page_seo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `meta_title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `meta_description` longtext COLLATE utf8_unicode_ci,
  `rel_author` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `rel_publisher` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ogTitle` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ogType` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ogImage` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ogUrl` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ogDescription` longtext COLLATE utf8_unicode_ci,
  `fbAdmins` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `twitterCard` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `twitterUrl` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `twitterTitle` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `twitterDescription` longtext COLLATE utf8_unicode_ci,
  `twitterImage` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `schemaPageType` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `schemaName` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `schemaDescription` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `schemaImage` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `meta_robots_index` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `meta_robots_follow` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `meta_robots_advanced` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `sitemap_indexed` tinyint(1) DEFAULT NULL,
  `sitemap_priority` double DEFAULT NULL,
  `rel_canonical` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `keyword` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `createdAt` datetime NOT NULL,
  `updatedAt` datetime NOT NULL,
  `page_id` int(11) DEFAULT NULL,
  `redirect_to` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_C9EC40DAC4663E4` (`page_id`),
  KEY `IDX_C9EC40DADC9332D9` (`redirect_to`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



# Affichage de la table vic_tag
# ------------------------------------------------------------

DROP TABLE IF EXISTS `vic_tag`;

CREATE TABLE `vic_tag` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



# Affichage de la table vic_user
# ------------------------------------------------------------

DROP TABLE IF EXISTS `vic_user`;

CREATE TABLE `vic_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `username_canonical` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `email_canonical` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `enabled` tinyint(1) NOT NULL,
  `salt` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `last_login` datetime DEFAULT NULL,
  `locked` tinyint(1) NOT NULL,
  `expired` tinyint(1) NOT NULL,
  `expires_at` datetime DEFAULT NULL,
  `confirmation_token` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `password_requested_at` datetime DEFAULT NULL,
  `roles` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:array)',
  `credentials_expired` tinyint(1) NOT NULL,
  `credentials_expire_at` datetime DEFAULT NULL,
  `firstname` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `lastname` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_89C762F392FC23A8` (`username_canonical`),
  UNIQUE KEY `UNIQ_89C762F3A0D96FBF` (`email_canonical`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

LOCK TABLES `vic_user` WRITE;
/*!40000 ALTER TABLE `vic_user` DISABLE KEYS */;

INSERT INTO `vic_user` (`id`, `username`, `username_canonical`, `email`, `email_canonical`, `enabled`, `salt`, `password`, `last_login`, `locked`, `expired`, `expires_at`, `confirmation_token`, `password_requested_at`, `roles`, `credentials_expired`, `credentials_expire_at`, `firstname`, `lastname`)
VALUES
  (42,'admin@appventus.com','admin@appventus.com','admin@appventus.com','admin@appventus.com',1,'twc3ppy3gxwkco8cccookcgw0ksco4s','seI2Hu7YdrlKnmbdxASZAbJPCBDJHXN2IRulYFcuiKoaHs5kcxvejzeuiqGr3GmNcddtg3Qwxhvrl0+xE2y9ww==',NULL,0,0,NULL,NULL,NULL,'a:2:{i:0;s:10:\"ROLE_ADMIN\";i:1;s:23:\"ROLE_VICTOIRE_DEVELOPER\";}',0,NULL,'Nimba','Admin'),
  (43,'leny@appventus.com','leny@appventus.com','leny@appventus.com','leny@appventus.com',1,'oa71wwpvaaokk4o444ckwss4wo0ss8','5i8xbx8kBrbDQG2eQhVQYeUwTwK4rmMzz9SpVF1Aa/6nddUr2bT225wvrMwtUtAXeuQeMw5CbHB3xphUzaLZGQ==','2014-10-13 16:37:42',0,0,NULL,NULL,NULL,'a:2:{i:0;s:10:\"ROLE_ADMIN\";i:1;s:23:\"ROLE_VICTOIRE_DEVELOPER\";}',0,NULL,'Leny','Bernard'),
  (44,'paul@appventus.com','paul@appventus.com','paul@appventus.com','paul@appventus.com',1,'kjaznldu01cokkk8s8840ggw4co4g8o','N07oxYZyq3hWMfurlkhN4NTUfkpKGBdXVYZd3VJNpsUNUW6PBIpZoqW7aXBOCMi72B407HZYYWHlXSI4bHXb7Q==','2014-11-03 11:00:14',0,0,NULL,NULL,NULL,'a:2:{i:0;s:10:\"ROLE_ADMIN\";i:1;s:23:\"ROLE_VICTOIRE_DEVELOPER\";}',0,NULL,'Paul','Andrieux'),
  (45,'loic@appventus.com','loic@appventus.com','loic@appventus.com','loic@appventus.com',1,'2bwj21bniv8k8k0swwgws0wkkc4c08c','Ne5kaqNH1agWkWIM0/HRs1l+uA0VfQqdE1L1OT2cgcMBhcBHyH64pZayNGkBGBkXnYCJuup6iLFYaGqctv4Tvw==','2014-10-13 11:30:04',0,0,NULL,NULL,NULL,'a:2:{i:0;s:10:\"ROLE_ADMIN\";i:1;s:23:\"ROLE_VICTOIRE_DEVELOPER\";}',0,NULL,'Loïc','Goyet'),
  (46,'charlie@appventus.com','charlie@appventus.com','charlie@appventus.com','charlie@appventus.com',1,'gso1ug88dnso4g08s0kgo4cgs84k4g4','wd7Abvc/yIFg4xRD+vwe72+e5/KVVYnxkhGsr5+eEclkoFssDiKEQ7Am0VfBmhu7tOWi4bQqVdgfCXlPwTyocg==',NULL,0,0,NULL,NULL,NULL,'a:2:{i:0;s:10:\"ROLE_ADMIN\";i:1;s:23:\"ROLE_VICTOIRE_DEVELOPER\";}',0,NULL,'Charlie','Lucas'),
  (47,'clement@appventus.com','clement@appventus.com','clement@appventus.com','clement@appventus.com',1,'8leubsg28ls84go4gggsc8ww0skc4c8','SOgDjLmUM7eK+EHT8N8HODihspt7xTYjAlV1g1AIhiDbHFpqh9KWlViBr71K0wO2rEdfJussa4d8LFB0zpuXjQ==','2014-10-06 12:08:59',0,0,NULL,NULL,NULL,'a:2:{i:0;s:10:\"ROLE_ADMIN\";i:1;s:23:\"ROLE_VICTOIRE_DEVELOPER\";}',0,NULL,'Clément','Menant');

/*!40000 ALTER TABLE `vic_user` ENABLE KEYS */;
UNLOCK TABLES;


# Affichage de la table vic_view
# ------------------------------------------------------------

DROP TABLE IF EXISTS `vic_view`;

CREATE TABLE `vic_view` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `layout` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `seo_id` int(11) DEFAULT NULL,
  `template_id` int(11) DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `author_id` int(11) DEFAULT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `bodyId` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `bodyClass` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `slug` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `homepage` tinyint(1) DEFAULT NULL,
  `compute_url` tinyint(1) DEFAULT NULL,
  `position` int(11) NOT NULL,
  `lft` int(11) NOT NULL,
  `lvl` int(11) NOT NULL,
  `rgt` int(11) NOT NULL,
  `root` int(11) DEFAULT NULL,
  `status` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `publishedAt` datetime DEFAULT NULL,
  `undeletable` tinyint(1) NOT NULL,
  `widget_map` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:array)',
  `createdAt` datetime NOT NULL,
  `updatedAt` datetime NOT NULL,
  `entityProxy_id` int(11) DEFAULT NULL,
  `type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `description` text COLLATE utf8_unicode_ci,
  `proxy_id` int(11) DEFAULT NULL,
  `business_entity_page_pattern_id` int(11) DEFAULT NULL,
  `query` longtext COLLATE utf8_unicode_ci,
  `business_entity_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `code` int(11) DEFAULT NULL,
  `visible_on_front` int(11) DEFAULT NULL,
  `image_id` int(11) DEFAULT NULL,
  `blog_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_D39C1B5DF47645AE` (`url`),
  KEY `IDX_D39C1B5D5DA0FB8` (`template_id`),
  KEY `IDX_D39C1B5D727ACA70` (`parent_id`),
  KEY `IDX_D39C1B5DF675F31B` (`author_id`),
  KEY `IDX_FAA91F3497E3DD86` (`seo_id`),
  KEY `IDX_FAA91F341341DB46` (`entityProxy_id`),
  KEY `IDX_FAA91F3412469DE2` (`category_id`),
  KEY `IDX_FAA91F34DB26A4E` (`proxy_id`),
  KEY `IDX_FAA91F34C60E513C` (`business_entity_page_pattern_id`),
  KEY `blog_id` (`blog_id`),
  CONSTRAINT `FK_D39C1B5D1341DB46` FOREIGN KEY (`entityProxy_id`) REFERENCES `vic_entity_proxy` (`id`),
  CONSTRAINT `FK_D39C1B5D727ACA70` FOREIGN KEY (`parent_id`) REFERENCES `vic_view` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_D39C1B5D97E3DD86` FOREIGN KEY (`seo_id`) REFERENCES `vic_page_seo` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_FAA91F3412469DE2` FOREIGN KEY (`category_id`) REFERENCES `vic_category` (`id`),
  CONSTRAINT `FK_FAA91F345DA0FB8` FOREIGN KEY (`template_id`) REFERENCES `vic_view` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_FAA91F34C60E513C` FOREIGN KEY (`business_entity_page_pattern_id`) REFERENCES `vic_view` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_FAA91F34DB26A4E` FOREIGN KEY (`proxy_id`) REFERENCES `vic_entity_proxy` (`id`),
  CONSTRAINT `vic_view_ibfk_1` FOREIGN KEY (`blog_id`) REFERENCES `vic_view` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

LOCK TABLES `vic_view` WRITE;
/*!40000 ALTER TABLE `vic_view` DISABLE KEYS */;

INSERT INTO `vic_view` (`id`, `layout`, `seo_id`, `template_id`, `parent_id`, `author_id`, `name`, `bodyId`, `bodyClass`, `slug`, `url`, `homepage`, `compute_url`, `position`, `lft`, `lvl`, `rgt`, `root`, `status`, `publishedAt`, `undeletable`, `widget_map`, `createdAt`, `updatedAt`, `entityProxy_id`, `type`, `category_id`, `description`, `proxy_id`, `business_entity_page_pattern_id`, `query`, `business_entity_name`, `code`, `visible_on_front`, `image_id`, `blog_id`)
VALUES
  (40,'layout',NULL,NULL,NULL,42,'Modèle de base',NULL,NULL,'base',NULL,NULL,NULL,0,1,0,2,40,NULL,NULL,1,'a:0:{}','2008-04-06 21:03:02','2004-01-19 20:41:34',NULL,'template',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),
  (41,'layout',NULL,40,NULL,42,'Modèle de page d\'accueil',NULL,NULL,'home',NULL,NULL,NULL,0,1,0,2,41,NULL,NULL,1,'a:0:{}','2010-09-28 18:01:46','1997-02-02 22:33:13',NULL,'template',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),
  (42,NULL,NULL,41,NULL,NULL,'Page d\'accueil',NULL,NULL,'home','',1,1,4,1,0,2,42,'published','1978-05-01 14:50:50',1,'a:0:{}','1997-07-30 11:13:10','1999-02-26 13:46:28',NULL,'page',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL);

/*!40000 ALTER TABLE `vic_view` ENABLE KEYS */;
UNLOCK TABLES;


# Affichage de la table vic_widget
# ------------------------------------------------------------

DROP TABLE IF EXISTS `vic_widget`;

CREATE TABLE `vic_widget` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `slot` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `theme` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fields` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:array)',
  `mode` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `query` longtext COLLATE utf8_unicode_ci,
  `business_entity_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `view_id` int(11) DEFAULT NULL,
  `entityProxy_id` int(11) DEFAULT NULL,
  `type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_57DF2B231341DB46` (`entityProxy_id`),
  KEY `IDX_57DF2B2331518C7` (`view_id`),
  CONSTRAINT `FK_57DF2B231341DB46` FOREIGN KEY (`entityProxy_id`) REFERENCES `vic_entity_proxy` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



# Affichage de la table vic_widget_alertbutton
# ------------------------------------------------------------

DROP TABLE IF EXISTS `vic_widget_alertbutton`;

CREATE TABLE `vic_widget_alertbutton` (
  `id` int(11) NOT NULL,
  `message` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `FK_76E2FC54BF396750` FOREIGN KEY (`id`) REFERENCES `vic_widget` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



# Affichage de la table vic_widget_breadcrumb
# ------------------------------------------------------------

DROP TABLE IF EXISTS `vic_widget_breadcrumb`;

CREATE TABLE `vic_widget_breadcrumb` (
  `id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `FK_AFC6BCBBBF396750` FOREIGN KEY (`id`) REFERENCES `vic_widget` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



# Affichage de la table vic_widget_button
# ------------------------------------------------------------

DROP TABLE IF EXISTS `vic_widget_button`;

CREATE TABLE `vic_widget_button` (
  `id` int(11) NOT NULL,
  `attached_widget_id` int(11) DEFAULT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `hoverTitle` varchar(55) COLLATE utf8_unicode_ci DEFAULT NULL,
  `size` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `style` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `target` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `route` varchar(55) COLLATE utf8_unicode_ci DEFAULT NULL,
  `route_parameters` longtext COLLATE utf8_unicode_ci COMMENT '(DC2Type:array)',
  `link_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `attached_page_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_D7442D949A25CE0` (`attached_page_id`),
  KEY `IDX_D7442D929E2283F` (`attached_widget_id`),
  CONSTRAINT `FK_D7442D929E2283F` FOREIGN KEY (`attached_widget_id`) REFERENCES `vic_widget` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_D7442D9BF396750` FOREIGN KEY (`id`) REFERENCES `vic_widget` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



# Affichage de la table vic_widget_ckeditor
# ------------------------------------------------------------

DROP TABLE IF EXISTS `vic_widget_ckeditor`;

CREATE TABLE `vic_widget_ckeditor` (
  `id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `FK_B264FED2BF396750` FOREIGN KEY (`id`) REFERENCES `vic_widget` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



# Affichage de la table vic_widget_filter
# ------------------------------------------------------------

DROP TABLE IF EXISTS `vic_widget_filter`;

CREATE TABLE `vic_widget_filter` (
  `id` int(11) NOT NULL,
  `list_id` int(11) DEFAULT NULL,
  `filters` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:array)',
  `ajax` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_48B6B1F93DAE168B` (`list_id`),
  CONSTRAINT `FK_48B6B1F93DAE168B` FOREIGN KEY (`list_id`) REFERENCES `vic_widget_listing` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_48B6B1F9BF396750` FOREIGN KEY (`id`) REFERENCES `vic_widget` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



# Affichage de la table vic_widget_image
# ------------------------------------------------------------

DROP TABLE IF EXISTS `vic_widget_image`;

CREATE TABLE `vic_widget_image` (
  `id` int(11) NOT NULL,
  `image_id` bigint(20) DEFAULT NULL,
  `alt` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `link_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `width` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `height` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `related_page_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_6FE810B43DA5256D` (`image_id`),
  KEY `IDX_6FE810B4335FA941` (`related_page_id`),
  CONSTRAINT `FK_6FE810B43DA5256D` FOREIGN KEY (`image_id`) REFERENCES `vic_media` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_6FE810B4BF396750` FOREIGN KEY (`id`) REFERENCES `vic_widget` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



# Affichage de la table vic_widget_listing
# ------------------------------------------------------------

DROP TABLE IF EXISTS `vic_widget_listing`;

CREATE TABLE `vic_widget_listing` (
  `id` int(11) NOT NULL,
  `targetPattern_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_6C501C5BC14172EE` (`targetPattern_id`),
  CONSTRAINT `FK_6C501C5BBF396750` FOREIGN KEY (`id`) REFERENCES `vic_widget` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



# Affichage de la table vic_widget_listing_item
# ------------------------------------------------------------

DROP TABLE IF EXISTS `vic_widget_listing_item`;

CREATE TABLE `vic_widget_listing_item` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `listing_id` int(11) DEFAULT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `position` int(11) NOT NULL,
  `type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_B2C649E3D4619D1A` (`listing_id`),
  CONSTRAINT `FK_B2C649E3D4619D1A` FOREIGN KEY (`listing_id`) REFERENCES `vic_widget_listing` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



# Affichage de la table vic_widget_menu
# ------------------------------------------------------------

DROP TABLE IF EXISTS `vic_widget_menu`;

CREATE TABLE `vic_widget_menu` (
  `id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `children_layout` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `FK_A25D8EFDBF396750` FOREIGN KEY (`id`) REFERENCES `vic_widget` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



# Affichage de la table vic_widget_menu_item
# ------------------------------------------------------------

DROP TABLE IF EXISTS `vic_widget_menu_item`;

CREATE TABLE `vic_widget_menu_item` (
  `id` int(11) NOT NULL,
  `menu_id` int(11) DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `attached_widget_id` int(11) DEFAULT NULL,
  `lft` int(11) NOT NULL,
  `lvl` int(11) NOT NULL,
  `rgt` int(11) NOT NULL,
  `root` int(11) DEFAULT NULL,
  `menu_type` int(11) NOT NULL,
  `url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `target` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `route` varchar(55) COLLATE utf8_unicode_ci DEFAULT NULL,
  `route_parameters` longtext COLLATE utf8_unicode_ci COMMENT '(DC2Type:array)',
  `link_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `root_hierarchy_page` int(11) DEFAULT NULL,
  `attached_page_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_7496369CCD7E912` (`menu_id`),
  KEY `IDX_7496369727ACA70` (`parent_id`),
  KEY `IDX_749636926D74311` (`root_hierarchy_page`),
  KEY `IDX_749636949A25CE0` (`attached_page_id`),
  KEY `IDX_749636929E2283F` (`attached_widget_id`),
  CONSTRAINT `FK_749636929E2283F` FOREIGN KEY (`attached_widget_id`) REFERENCES `vic_widget` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_7496369727ACA70` FOREIGN KEY (`parent_id`) REFERENCES `vic_widget_menu_item` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_7496369BF396750` FOREIGN KEY (`id`) REFERENCES `vic_widget_listing_item` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_7496369CCD7E912` FOREIGN KEY (`menu_id`) REFERENCES `vic_widget_menu` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



# Affichage de la table vic_widget_render
# ------------------------------------------------------------

DROP TABLE IF EXISTS `vic_widget_render`;

CREATE TABLE `vic_widget_render` (
  `id` int(11) NOT NULL,
  `related_widget_id` int(11) DEFAULT NULL,
  `route` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `params` longtext COLLATE utf8_unicode_ci COMMENT '(DC2Type:array)',
  `kind` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_A32E778E5329D9F9` (`related_widget_id`),
  CONSTRAINT `FK_A32E778E5329D9F9` FOREIGN KEY (`related_widget_id`) REFERENCES `vic_widget` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_A32E778EBF396750` FOREIGN KEY (`id`) REFERENCES `vic_widget` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



# Affichage de la table vic_widget_simplecontactform
# ------------------------------------------------------------

DROP TABLE IF EXISTS `vic_widget_simplecontactform`;

CREATE TABLE `vic_widget_simplecontactform` (
  `id` int(11) NOT NULL,
  `recipientName` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `recipientEmail` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `replyToEmail` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `senderEmail` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `subject` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `FK_CD873B5BF396750` FOREIGN KEY (`id`) REFERENCES `vic_widget` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



# Affichage de la table vic_widget_simplecontactform_message
# ------------------------------------------------------------

DROP TABLE IF EXISTS `vic_widget_simplecontactform_message`;

CREATE TABLE `vic_widget_simplecontactform_message` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `widget_id` int(11) DEFAULT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `content` longtext COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_BAFEB034FBE885E2` (`widget_id`),
  CONSTRAINT `FK_BAFEB034FBE885E2` FOREIGN KEY (`widget_id`) REFERENCES `vic_widget` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



# Affichage de la table vic_widget_slider
# ------------------------------------------------------------

DROP TABLE IF EXISTS `vic_widget_slider`;

CREATE TABLE `vic_widget_slider` (
  `id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `FK_F8B5FEE3BF396750` FOREIGN KEY (`id`) REFERENCES `vic_widget` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



# Affichage de la table vic_widget_slider_item
# ------------------------------------------------------------

DROP TABLE IF EXISTS `vic_widget_slider_item`;

CREATE TABLE `vic_widget_slider_item` (
  `id` int(11) NOT NULL,
  `image_id` bigint(20) DEFAULT NULL,
  `listing_id` int(11) DEFAULT NULL,
  `attached_widget_id` int(11) DEFAULT NULL,
  `link_label` varchar(55) COLLATE utf8_unicode_ci DEFAULT NULL,
  `url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `target` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `route` varchar(55) COLLATE utf8_unicode_ci DEFAULT NULL,
  `route_parameters` longtext COLLATE utf8_unicode_ci COMMENT '(DC2Type:array)',
  `link_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `attached_page_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_5403DED03DA5256D` (`image_id`),
  KEY `IDX_5403DED0D4619D1A` (`listing_id`),
  KEY `IDX_5403DED049A25CE0` (`attached_page_id`),
  KEY `IDX_5403DED029E2283F` (`attached_widget_id`),
  CONSTRAINT `FK_5403DED029E2283F` FOREIGN KEY (`attached_widget_id`) REFERENCES `vic_widget` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_5403DED03DA5256D` FOREIGN KEY (`image_id`) REFERENCES `vic_media` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_5403DED0BF396750` FOREIGN KEY (`id`) REFERENCES `vic_widget_listing_item` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_5403DED0D4619D1A` FOREIGN KEY (`listing_id`) REFERENCES `vic_widget_slider` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



# Affichage de la table vic_widget_text
# ------------------------------------------------------------

DROP TABLE IF EXISTS `vic_widget_text`;

CREATE TABLE `vic_widget_text` (
  `id` int(11) NOT NULL,
  `content` longtext COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  CONSTRAINT `FK_E4D313A9BF396750` FOREIGN KEY (`id`) REFERENCES `vic_widget` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



# Affichage de la table vic_widget_title
# ------------------------------------------------------------

DROP TABLE IF EXISTS `vic_widget_title`;

CREATE TABLE `vic_widget_title` (
  `id` int(11) NOT NULL,
  `headingLevel` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `headingStyle` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `align` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `FK_81E36C80BF396750` FOREIGN KEY (`id`) REFERENCES `vic_widget` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;




/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
