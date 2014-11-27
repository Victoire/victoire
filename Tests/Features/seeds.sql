# ************************************************************
# Sequel Pro SQL dump
# Version 4326
#
# http://www.sequelpro.com/
# http://code.google.com/p/sequel-pro/
#
# Hôte: localhost (MySQL 5.5.34)
# Base de données: victoire
# Temps de génération: 2014-11-27 13:59:19 +0000
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
  KEY `IDX_2E6D5EDA7294869C` (`article_id`),
  KEY `IDX_2E6D5EDABAD26311` (`tag_id`),
  CONSTRAINT `FK_2E6D5EDA7294869C` FOREIGN KEY (`article_id`) REFERENCES `vic_view` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_2E6D5EDABAD26311` FOREIGN KEY (`tag_id`) REFERENCES `vic_tag` (`id`) ON DELETE CASCADE
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
  PRIMARY KEY (`id`)
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

LOCK TABLES `vic_media_folders` WRITE;
/*!40000 ALTER TABLE `vic_media_folders` DISABLE KEYS */;

INSERT INTO `vic_media_folders` (`id`, `parent_id`, `name`, `created_at`, `updated_at`, `rel`, `internal_name`, `deleted`)
VALUES
  (2,NULL,'/','1990-12-15 21:52:25','2008-01-02 06:43:01',NULL,NULL,0);

/*!40000 ALTER TABLE `vic_media_folders` ENABLE KEYS */;
UNLOCK TABLES;


# Affichage de la table vic_page_seo
# ------------------------------------------------------------

DROP TABLE IF EXISTS `vic_page_seo`;

CREATE TABLE `vic_page_seo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `page_id` int(11) DEFAULT NULL,
  `redirect_to` int(11) DEFAULT NULL,
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
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_C9EC40DAC4663E4` (`page_id`),
  KEY `IDX_C9EC40DADC9332D9` (`redirect_to`),
  CONSTRAINT `FK_C9EC40DAC4663E4` FOREIGN KEY (`page_id`) REFERENCES `vic_view` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_C9EC40DADC9332D9` FOREIGN KEY (`redirect_to`) REFERENCES `vic_view` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



# Affichage de la table vic_route_history
# ------------------------------------------------------------

DROP TABLE IF EXISTS `vic_route_history`;

CREATE TABLE `vic_route_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `page_id` int(11) DEFAULT NULL,
  `url` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `createdAt` datetime NOT NULL,
  `updatedAt` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_1C14303FC4663E4` (`page_id`),
  CONSTRAINT `FK_1C14303FC4663E4` FOREIGN KEY (`page_id`) REFERENCES `vic_view` (`id`) ON DELETE CASCADE
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
  `createdAt` datetime NOT NULL,
  `updatedAt` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_89C762F392FC23A8` (`username_canonical`),
  UNIQUE KEY `UNIQ_89C762F3A0D96FBF` (`email_canonical`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

LOCK TABLES `vic_user` WRITE;
/*!40000 ALTER TABLE `vic_user` DISABLE KEYS */;

INSERT INTO `vic_user` (`id`, `username`, `username_canonical`, `email`, `email_canonical`, `enabled`, `salt`, `password`, `last_login`, `locked`, `expired`, `expires_at`, `confirmation_token`, `password_requested_at`, `roles`, `credentials_expired`, `credentials_expire_at`, `firstname`, `lastname`, `createdAt`, `updatedAt`)
VALUES
  (7,'admin@appventus.com','admin@appventus.com','admin@appventus.com','admin@appventus.com',1,'q3e02b4xay8oso4wwws04gw0csksook','2AnHBWr6CZJI+kEqOa4TijrDf4i0ip5gr1rhF3O1pBoGW41e7wtU7P+fTAbg0Yg21vfzk1NiH/fVjCOn9tVezQ==',NULL,0,0,NULL,NULL,NULL,'a:2:{i:0;s:10:\"ROLE_ADMIN\";i:1;s:23:\"ROLE_VICTOIRE_DEVELOPER\";}',0,NULL,'Nimba','Admin','2001-07-25 10:59:13','2014-11-11 04:28:04'),
  (8,'leny@appventus.com','leny@appventus.com','leny@appventus.com','leny@appventus.com',1,'p2vwfqisrdc80048wsg888wcscw80k8','G6j4WQS2RXUzDXi0SGCuBavpzVPsp3OTu8yD3Dp91dycSc/b6DD0hk2PUHYwyCaxAFeGpf/dS00vDWzCZ3UNSA==','2014-11-27 12:21:01',0,0,NULL,NULL,NULL,'a:2:{i:0;s:10:\"ROLE_ADMIN\";i:1;s:23:\"ROLE_VICTOIRE_DEVELOPER\";}',0,NULL,'Leny','Bernard','2008-06-02 00:16:06','2004-03-09 19:33:21'),
  (9,'paul@appventus.com','paul@appventus.com','paul@appventus.com','paul@appventus.com',1,'ly15nvewv2ocg4gwgo8oock84coss0k','OzUJcKBqXv1OqDRqOrV+ecoVXgy0N5Pj+rnpuPVJ31FQy2OzUskMq7AEI3tIKZ2tK/JrFvLVxWUuGenSQQhbhw==',NULL,0,0,NULL,NULL,NULL,'a:2:{i:0;s:10:\"ROLE_ADMIN\";i:1;s:23:\"ROLE_VICTOIRE_DEVELOPER\";}',0,NULL,'Paul','Andrieux','2010-11-27 12:28:25','1997-03-14 16:23:08'),
  (10,'loic@appventus.com','loic@appventus.com','loic@appventus.com','loic@appventus.com',1,'53xq5ou52z8c040o8k88sws0w880kc8','QlHzFPB8oezACFcC/2G8u0DJcPI7vm2IShZGmlCviT2SLDbVy2Yt2QI2N+ZNCMRBFCmNOex9dtgpBBi/iSqoWw==',NULL,0,0,NULL,NULL,NULL,'a:2:{i:0;s:10:\"ROLE_ADMIN\";i:1;s:23:\"ROLE_VICTOIRE_DEVELOPER\";}',0,NULL,'Loïc','Goyet','1978-05-13 20:08:36','1997-09-08 22:09:52'),
  (11,'charlie@appventus.com','charlie@appventus.com','charlie@appventus.com','charlie@appventus.com',1,'3e9w6d537pyc0c4o408ow44s00gook0','714nUbaWAzcPGom2lerNLLz/lDGg3ZAfwLKZ4sMVnBL2PtMyOEup58N9zNMIHnW8gc2qdghpmFArvPMjKKinhQ==',NULL,0,0,NULL,NULL,NULL,'a:2:{i:0;s:10:\"ROLE_ADMIN\";i:1;s:23:\"ROLE_VICTOIRE_DEVELOPER\";}',0,NULL,'Charlie','Lucas','1999-04-10 08:15:14','1985-01-24 13:42:32'),
  (12,'clement@appventus.com','clement@appventus.com','clement@appventus.com','clement@appventus.com',1,'6kua0k1tzfcwcwwsocgok48gg0wcoo0','xduYNzduo9BJHJlaZkzzf3DxFqbLXTQcYYL1Rzzz531igb9RD+CWL7OuQiik108Spx3Zc4Zr0HUU3N0NmYLDkA==',NULL,0,0,NULL,NULL,NULL,'a:2:{i:0;s:10:\"ROLE_ADMIN\";i:1;s:23:\"ROLE_VICTOIRE_DEVELOPER\";}',0,NULL,'Clément','Menant','1987-10-26 08:27:39','2012-01-04 16:53:38');

/*!40000 ALTER TABLE `vic_user` ENABLE KEYS */;
UNLOCK TABLES;


# Affichage de la table vic_view
# ------------------------------------------------------------

DROP TABLE IF EXISTS `vic_view`;

CREATE TABLE `vic_view` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `template_id` int(11) DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `blog_id` int(11) DEFAULT NULL,
  `image_id` bigint(20) DEFAULT NULL,
  `proxy_id` int(11) DEFAULT NULL,
  `seo_id` int(11) DEFAULT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `bodyId` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `bodyClass` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `slug` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `position` int(11) NOT NULL,
  `lft` int(11) NOT NULL,
  `lvl` int(11) NOT NULL,
  `rgt` int(11) NOT NULL,
  `root` int(11) DEFAULT NULL,
  `undeletable` tinyint(1) NOT NULL,
  `widget_map` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:array)',
  `createdAt` datetime NOT NULL,
  `updatedAt` datetime NOT NULL,
  `type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `code` int(11) DEFAULT NULL,
  `description` longtext COLLATE utf8_unicode_ci,
  `visible_on_front` tinyint(1) DEFAULT NULL,
  `entityProxy_id` int(11) DEFAULT NULL,
  `query` longtext COLLATE utf8_unicode_ci,
  `business_entity_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `compute_url` tinyint(1) DEFAULT NULL,
  `status` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `publishedAt` datetime DEFAULT NULL,
  `homepage` tinyint(1) DEFAULT NULL,
  `layout` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `author_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_FAA91F3477153098` (`code`),
  UNIQUE KEY `UNIQ_FAA91F34F47645AE` (`url`),
  KEY `IDX_FAA91F345DA0FB8` (`template_id`),
  KEY `IDX_FAA91F34727ACA70` (`parent_id`),
  KEY `IDX_FAA91F3412469DE2` (`category_id`),
  KEY `IDX_FAA91F34DAE07E97` (`blog_id`),
  KEY `IDX_FAA91F343DA5256D` (`image_id`),
  KEY `IDX_FAA91F34DB26A4E` (`proxy_id`),
  KEY `IDX_FAA91F341341DB46` (`entityProxy_id`),
  KEY `IDX_FAA91F3497E3DD86` (`seo_id`),
  KEY `IDX_FAA91F34F675F31B` (`author_id`),
  CONSTRAINT `FK_FAA91F34F675F31B` FOREIGN KEY (`author_id`) REFERENCES `vic_user` (`id`),
  CONSTRAINT `FK_FAA91F3412469DE2` FOREIGN KEY (`category_id`) REFERENCES `vic_category` (`id`),
  CONSTRAINT `FK_FAA91F341341DB46` FOREIGN KEY (`entityProxy_id`) REFERENCES `vic_entity_proxy` (`id`),
  CONSTRAINT `FK_FAA91F343DA5256D` FOREIGN KEY (`image_id`) REFERENCES `vic_media` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_FAA91F345DA0FB8` FOREIGN KEY (`template_id`) REFERENCES `vic_view` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_FAA91F34727ACA70` FOREIGN KEY (`parent_id`) REFERENCES `vic_view` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_FAA91F3497E3DD86` FOREIGN KEY (`seo_id`) REFERENCES `vic_page_seo` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_FAA91F34DAE07E97` FOREIGN KEY (`blog_id`) REFERENCES `vic_view` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_FAA91F34DB26A4E` FOREIGN KEY (`proxy_id`) REFERENCES `vic_entity_proxy` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

LOCK TABLES `vic_view` WRITE;
/*!40000 ALTER TABLE `vic_view` DISABLE KEYS */;

INSERT INTO `vic_view` (`id`, `template_id`, `parent_id`, `category_id`, `blog_id`, `image_id`, `proxy_id`, `seo_id`, `name`, `bodyId`, `bodyClass`, `slug`, `position`, `lft`, `lvl`, `rgt`, `root`, `undeletable`, `widget_map`, `createdAt`, `updatedAt`, `type`, `code`, `description`, `visible_on_front`, `entityProxy_id`, `query`, `business_entity_name`, `url`, `compute_url`, `status`, `publishedAt`, `homepage`, `layout`, `author_id`)
VALUES
  (4,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Modèle de base',NULL,NULL,'base',0,1,0,2,4,1,'a:0:{}','1988-10-28 04:14:41','1984-01-25 23:23:28','template',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'fullWidth',NULL),
  (5,4,NULL,NULL,NULL,NULL,NULL,NULL,'Modèle de page d\'accueil',NULL,NULL,'home',0,1,0,2,5,1,'a:0:{}','1984-05-19 18:21:40','1993-07-22 01:46:18','template',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'home',NULL),
  (6,5,NULL,NULL,NULL,NULL,NULL,NULL,'Page d\'accueil',NULL,NULL,'home',0,1,0,2,6,1,'a:0:{}','1995-02-04 21:42:54','2009-06-06 22:28:02','page',NULL,NULL,NULL,NULL,NULL,NULL,'',1,'published','1979-03-08 04:55:56',1,NULL,NULL),
  (7,4,NULL,NULL,NULL,NULL,NULL,NULL,'Mauvaise Requete',NULL,NULL,'mauvaise-requete',0,1,0,2,7,0,'a:0:{}','2013-10-11 19:12:52','1990-10-04 23:57:37','errorpage',400,NULL,NULL,NULL,NULL,NULL,'mauvaise-requete',1,'published','2004-06-08 14:17:13',NULL,NULL,NULL),
  (8,4,NULL,NULL,NULL,NULL,NULL,NULL,'Interdit',NULL,NULL,'erreur403',0,1,0,2,8,0,'a:0:{}','2011-01-15 19:41:23','1988-09-27 04:24:40','errorpage',403,NULL,NULL,NULL,NULL,NULL,'erreur403',1,'published','1984-12-11 16:07:59',NULL,NULL,NULL),
  (9,4,NULL,NULL,NULL,NULL,NULL,NULL,'Page introuvable',NULL,NULL,'erreur404',0,1,0,2,9,0,'a:0:{}','1989-12-02 21:57:01','1995-05-28 23:43:10','errorpage',404,NULL,NULL,NULL,NULL,NULL,'erreur404',1,'published','1994-06-28 09:44:29',NULL,NULL,NULL),
  (10,4,NULL,NULL,NULL,NULL,NULL,NULL,'Erreur serveur',NULL,NULL,'erreur500',0,1,0,2,10,0,'a:0:{}','2012-03-03 13:29:42','2005-10-21 18:55:06','errorpage',500,NULL,NULL,NULL,NULL,NULL,'erreur500',1,'published','2008-11-01 12:36:41',NULL,NULL,NULL),
  (11,4,NULL,NULL,NULL,NULL,NULL,NULL,'Service indisponible',NULL,NULL,'erreur503',0,1,0,2,11,0,'a:0:{}','1978-11-04 22:39:54','2002-02-24 13:50:48','errorpage',503,NULL,NULL,NULL,NULL,NULL,'erreur503',1,'published','1980-02-22 16:20:08',NULL,NULL,NULL);

/*!40000 ALTER TABLE `vic_view` ENABLE KEYS */;
UNLOCK TABLES;


# Affichage de la table vic_widget
# ------------------------------------------------------------

DROP TABLE IF EXISTS `vic_widget`;

CREATE TABLE `vic_widget` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `view_id` int(11) DEFAULT NULL,
  `slot` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `theme` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fields` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:array)',
  `mode` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `query` longtext COLLATE utf8_unicode_ci,
  `business_entity_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `entityProxy_id` int(11) DEFAULT NULL,
  `type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_57DF2B231341DB46` (`entityProxy_id`),
  KEY `IDX_57DF2B2331518C7` (`view_id`),
  CONSTRAINT `FK_57DF2B231341DB46` FOREIGN KEY (`entityProxy_id`) REFERENCES `vic_entity_proxy` (`id`),
  CONSTRAINT `FK_57DF2B2331518C7` FOREIGN KEY (`view_id`) REFERENCES `vic_view` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


# Affichage de la table vic_widget_anakin
# ------------------------------------------------------------

DROP TABLE IF EXISTS `vic_widget_anakin`;

CREATE TABLE `vic_widget_anakin` (
  `id` int(11) NOT NULL,
  `side` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `FK_BA377282BF396750` FOREIGN KEY (`id`) REFERENCES `vic_widget` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
