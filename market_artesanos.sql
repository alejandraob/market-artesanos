-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: market_artesanos
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `artisan_category`
--

DROP TABLE IF EXISTS `artisan_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `artisan_category` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `artisan_id` bigint(20) unsigned NOT NULL,
  `category_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `artisan_category_artisan_id_category_id_unique` (`artisan_id`,`category_id`),
  KEY `artisan_category_category_id_foreign` (`category_id`),
  CONSTRAINT `artisan_category_artisan_id_foreign` FOREIGN KEY (`artisan_id`) REFERENCES `artisans` (`id`) ON DELETE CASCADE,
  CONSTRAINT `artisan_category_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `artisan_category`
--

LOCK TABLES `artisan_category` WRITE;
/*!40000 ALTER TABLE `artisan_category` DISABLE KEYS */;
INSERT INTO `artisan_category` VALUES (1,10,15,'2026-03-17 18:07:59','2026-03-17 18:07:59');
/*!40000 ALTER TABLE `artisan_category` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `artisans`
--

DROP TABLE IF EXISTS `artisans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `artisans` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `bio` text DEFAULT NULL,
  `specialty` varchar(255) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `artisans_user_id_foreign` (`user_id`),
  CONSTRAINT `artisans_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `artisans`
--

LOCK TABLES `artisans` WRITE;
/*!40000 ALTER TABLE `artisans` DISABLE KEYS */;
INSERT INTO `artisans` VALUES (1,1,'Líder de la asociación y tejedor experto.','Tejido Ancestral','Catriel',NULL,1,'2026-03-17 17:00:29','2026-03-17 17:00:29'),(2,5,NULL,'Tejido Crochet, Tejido a Dos Agujas y Bordado a Mano',NULL,NULL,1,'2026-03-17 18:05:25','2026-03-17 18:05:25'),(3,6,NULL,'Bijouteria, Pintura Decorativa y Manualidades',NULL,NULL,1,'2026-03-17 18:05:25','2026-03-17 18:05:25'),(4,7,NULL,'Bordado a Mano y Textil',NULL,NULL,1,'2026-03-17 18:05:26','2026-03-17 18:05:26'),(5,8,NULL,'Ceramica, Manualidades, Bijouteria y Joyeria en Alpaca',NULL,NULL,1,'2026-03-17 18:05:26','2026-03-17 18:05:26'),(6,9,NULL,'Telar Ancestral y Fotografia de Naturaleza',NULL,NULL,1,'2026-03-17 18:05:26','2026-03-17 18:05:26'),(7,10,NULL,'Textil, Accesorios en Tela',NULL,NULL,1,'2026-03-17 18:05:26','2026-03-17 18:05:26'),(8,11,NULL,'Joyeria Artesanal, Ceramica, Pintura Decorativa',NULL,NULL,1,'2026-03-17 18:05:26','2026-03-17 18:05:26'),(9,12,NULL,'Macrame, Alambrismo',NULL,NULL,1,'2026-03-17 18:05:26','2026-03-17 18:05:26'),(10,13,NULL,'Cuero, Textil y Madera','Catriel, Río Negro','artisans/i79giB1ESoxO5RLxpQL5liglkZDgrsaeXou2q45G.jpg',1,'2026-03-17 18:05:26','2026-03-17 18:24:59'),(11,14,NULL,'Tejidos con Totora',NULL,NULL,1,'2026-03-17 18:05:26','2026-03-17 18:05:26'),(12,15,NULL,'Madera, Cuero y Herreria',NULL,NULL,1,'2026-03-17 18:05:26','2026-03-17 18:05:26'),(13,16,NULL,'Munecos en Tela, Porcelana Fria y Manualidades',NULL,NULL,1,'2026-03-17 18:05:26','2026-03-17 18:05:26'),(14,17,NULL,'Tejido Telar Maria y Textil',NULL,NULL,1,'2026-03-17 18:05:26','2026-03-17 18:05:26'),(15,18,NULL,'Sahumerios Artesanales',NULL,NULL,1,'2026-03-17 18:05:26','2026-03-17 18:05:26'),(16,19,NULL,'Bijouteria en Macrame, Munecos de Tela, Accesorios para Bebe, Textil y Madera',NULL,NULL,1,'2026-03-17 18:05:26','2026-03-17 18:05:26');
/*!40000 ALTER TABLE `artisans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cart_items`
--

DROP TABLE IF EXISTS `cart_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cart_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `cart_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cart_items_cart_id_foreign` (`cart_id`),
  KEY `cart_items_product_id_foreign` (`product_id`),
  CONSTRAINT `cart_items_cart_id_foreign` FOREIGN KEY (`cart_id`) REFERENCES `carts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cart_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cart_items`
--

LOCK TABLES `cart_items` WRITE;
/*!40000 ALTER TABLE `cart_items` DISABLE KEYS */;
INSERT INTO `cart_items` VALUES (4,3,2,1,'2026-03-17 19:08:58','2026-03-17 19:08:58');
/*!40000 ALTER TABLE `cart_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `carts`
--

DROP TABLE IF EXISTS `carts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `carts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `session_id` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `carts_user_id_foreign` (`user_id`),
  KEY `carts_session_id_index` (`session_id`),
  CONSTRAINT `carts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `carts`
--

LOCK TABLES `carts` WRITE;
/*!40000 ALTER TABLE `carts` DISABLE KEYS */;
INSERT INTO `carts` VALUES (1,NULL,'f1e34f51-2cd1-4721-80c8-22c5ba0e3aac','2026-03-17 17:01:23','2026-03-17 17:01:23'),(2,1,'f1e34f51-2cd1-4721-80c8-22c5ba0e3aac','2026-03-17 17:09:04','2026-03-17 17:09:04'),(3,4,'f1e34f51-2cd1-4721-80c8-22c5ba0e3aac','2026-03-17 19:04:54','2026-03-17 19:04:54');
/*!40000 ALTER TABLE `carts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `icon` varchar(255) DEFAULT NULL,
  `parent_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `categories_slug_unique` (`slug`),
  KEY `categories_parent_id_foreign` (`parent_id`),
  CONSTRAINT `categories_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (1,'Textiles y Tejidos','textiles-y-tejidos',NULL,NULL,NULL,'2026-03-17 17:00:29','2026-03-17 17:00:29'),(2,'Crochet / Dos Agujas','crochet-dos-agujas',NULL,NULL,1,'2026-03-17 17:00:29','2026-03-17 17:00:29'),(3,'Telar Ancestral','telar-ancestral',NULL,NULL,1,'2026-03-17 17:00:29','2026-03-17 17:00:29'),(4,'Bordado','bordado',NULL,NULL,1,'2026-03-17 17:00:29','2026-03-17 17:00:29'),(5,'Accesorios Textiles','accesorios-textiles',NULL,NULL,1,'2026-03-17 17:00:29','2026-03-17 17:00:29'),(6,'Joyería y Accesorios','joyeria-y-accesorios',NULL,NULL,NULL,'2026-03-17 17:00:29','2026-03-17 17:00:29'),(7,'Joyería en Alpaca','joyeria-en-alpaca',NULL,NULL,6,'2026-03-17 17:00:29','2026-03-17 17:00:29'),(8,'Macramé / Alambrismo','macrame-alambrismo',NULL,NULL,6,'2026-03-17 17:00:29','2026-03-17 17:00:29'),(9,'Bijouterie','bijouterie',NULL,NULL,6,'2026-03-17 17:00:29','2026-03-17 17:00:29'),(10,'Hogar y Decoración','hogar-y-decoracion',NULL,NULL,NULL,'2026-03-17 17:00:29','2026-03-17 17:00:29'),(11,'Cerámica','ceramica',NULL,NULL,10,'2026-03-17 17:00:29','2026-03-17 17:00:29'),(12,'Pintura Decorativa','pintura-decorativa',NULL,NULL,10,'2026-03-17 17:00:29','2026-03-17 17:00:29'),(13,'Porcelana Fría','porcelana-fria',NULL,NULL,10,'2026-03-17 17:00:29','2026-03-17 17:00:29'),(14,'Sahumerios','sahumerios',NULL,NULL,10,'2026-03-17 17:00:29','2026-03-17 17:00:29'),(15,'Materiales Nobles','materiales-nobles',NULL,NULL,NULL,'2026-03-17 17:00:29','2026-03-17 17:00:29'),(16,'Carpintería','carpinteria',NULL,NULL,15,'2026-03-17 17:00:29','2026-03-17 17:00:29'),(17,'Marroquinería','marroquineria',NULL,NULL,15,'2026-03-17 17:00:29','2026-03-17 17:00:29'),(18,'Herrería de Autor','herreria-de-autor',NULL,NULL,15,'2026-03-17 17:00:29','2026-03-17 17:00:29'),(19,'Arte y Juguetes','arte-y-juguetes',NULL,NULL,NULL,'2026-03-17 17:00:29','2026-03-17 17:00:29'),(20,'Muñecos de Tela','munecos-de-tela',NULL,NULL,19,'2026-03-17 17:00:29','2026-03-17 17:00:29'),(21,'Fotografía de Naturaleza','fotografia-de-naturaleza',NULL,NULL,19,'2026-03-17 17:00:29','2026-03-17 17:00:29');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'2014_10_12_000000_create_users_table',1),(2,'2014_10_12_100000_create_password_reset_tokens_table',1),(3,'2019_08_19_000000_create_failed_jobs_table',1),(4,'2019_12_14_000001_create_personal_access_tokens_table',1),(5,'2026_03_16_135418_create_permission_tables',1),(6,'2026_03_16_135501_create_categories_table',1),(7,'2026_03_16_135502_create_artisans_table',1),(8,'2026_03_16_135502_create_products_table',1),(9,'2026_03_16_135503_create_orders_table',1),(10,'2026_03_16_135504_create_carts_table',1),(11,'2026_03_16_135504_create_order_items_table',1),(12,'2026_03_16_135505_create_cart_items_table',1),(13,'2026_03_17_000001_add_photo_to_artisans_and_is_active_to_products',2),(14,'2026_03_17_000002_create_artisan_category_table',3);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `model_has_permissions`
--

DROP TABLE IF EXISTS `model_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) unsigned NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `model_has_permissions`
--

LOCK TABLES `model_has_permissions` WRITE;
/*!40000 ALTER TABLE `model_has_permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `model_has_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `model_has_roles`
--

DROP TABLE IF EXISTS `model_has_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `model_has_roles` (
  `role_id` bigint(20) unsigned NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `model_has_roles`
--

LOCK TABLES `model_has_roles` WRITE;
/*!40000 ALTER TABLE `model_has_roles` DISABLE KEYS */;
/*!40000 ALTER TABLE `model_has_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_items_order_id_foreign` (`order_id`),
  KEY `order_items_product_id_foreign` (`product_id`),
  CONSTRAINT `order_items_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_items`
--

LOCK TABLES `order_items` WRITE;
/*!40000 ALTER TABLE `order_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `orders` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `status` enum('pending','paid','shipped','delivered','cancelled') NOT NULL DEFAULT 'pending',
  `payment_id` varchar(255) DEFAULT NULL,
  `shipping_tracking` varchar(255) DEFAULT NULL,
  `shipping_cost` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `orders_user_id_foreign` (`user_id`),
  CONSTRAINT `orders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permissions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal_access_tokens`
--

LOCK TABLES `personal_access_tokens` WRITE;
/*!40000 ALTER TABLE `personal_access_tokens` DISABLE KEYS */;
INSERT INTO `personal_access_tokens` VALUES (1,'App\\Models\\User',1,'auth_token','8bea71464591b51c087d664039f9ac424e0636df4ccffff780ab426f3150ef58','[\"*\"]','2026-03-17 19:04:29',NULL,'2026-03-17 17:08:46','2026-03-17 19:04:29'),(2,'App\\Models\\User',4,'auth_token','da92118b65b504f98ff55bde33b8f706117f2917905fc881e02fe28ac936d2a7','[\"*\"]','2026-03-17 19:09:00',NULL,'2026-03-17 19:04:36','2026-03-17 19:09:00'),(3,'App\\Models\\User',1,'auth_token','0b58e1b3073d2bd56f2fedf6658371ccf6949f7fdcfa618c865c98b40d3f745a','[\"*\"]','2026-03-18 14:16:31',NULL,'2026-03-18 14:16:24','2026-03-18 14:16:31');
/*!40000 ALTER TABLE `personal_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `products` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `artisan_id` bigint(20) unsigned NOT NULL,
  `category_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `images` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`images`)),
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `products_slug_unique` (`slug`),
  KEY `products_artisan_id_foreign` (`artisan_id`),
  KEY `products_category_id_foreign` (`category_id`),
  CONSTRAINT `products_artisan_id_foreign` FOREIGN KEY (`artisan_id`) REFERENCES `artisans` (`id`) ON DELETE CASCADE,
  CONSTRAINT `products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (1,3,1,'Llamador de Angeles tejido','llamador-de-angeles-tejido-69b97aef5e620','Aro de 50cm \r\nTejido de lana, forma mandala',25000.00,1,'[\"products\\/07anj269IVmm3ahypD50h21LF7eGaL6khn4mc4X2.jpg\"]',0,1,'2026-03-17 18:31:34','2026-03-17 19:01:51'),(2,7,10,'Almohadón decorativo Búho','almohadon-decorativo-buho-69b97b76dad66','Almohadón en forma de Búho tamaño 25cm \r\nmaterial tela',18500.00,1,'[\"products\\/yhMSuXd2C3WWHWA7uWaX55hfJeyrEEt3EKdIKpP5.jpg\"]',0,1,'2026-03-17 19:04:06','2026-03-17 19:04:06');
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_has_permissions`
--

DROP TABLE IF EXISTS `role_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) unsigned NOT NULL,
  `role_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_has_permissions`
--

LOCK TABLES `role_has_permissions` WRITE;
/*!40000 ALTER TABLE `role_has_permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `role_has_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','presidente','cliente') NOT NULL DEFAULT 'cliente',
  `avatar` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `firebase_token` varchar(255) DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Presidente de Artesanos','admin@artesanos.com',NULL,'$2y$10$EPkA9QnogXBhgY.fGlsXi.nEJNfK26VZkI8KZAYYCGiu2mLYfS0Y6','presidente',NULL,NULL,NULL,NULL,'2026-03-17 17:00:29','2026-03-17 17:00:29'),(2,'Juan Perez','juan@example.com',NULL,'$2y$10$v8rkj/mSeGL5l/UkJkaSs.K3M662woYoSWsndY1dapPBobvj4s1LG','cliente',NULL,NULL,NULL,NULL,'2026-03-17 17:00:29','2026-03-17 17:00:29'),(4,'Cliente Artesanos','cliente@artesanos.com',NULL,'$2y$10$NO4g50gY/o0tZlQOy93.0.9nx0lq83tifLj.xcwN3Y8ynLqHxKDbu','cliente',NULL,NULL,NULL,NULL,'2026-03-17 17:03:54','2026-03-17 17:03:54'),(5,'Albornoz Nelly','albornoz.nelly@artesanos.com',NULL,'$2y$10$XmXY9pbBVDEpyrJt/0MEwO6v4PETGBef3cgr7SgRfe/XTU92KcNDu','cliente',NULL,'2996348416',NULL,NULL,'2026-03-17 18:05:25','2026-03-17 18:05:25'),(6,'Atencio Carla','atencio.carla@artesanos.com',NULL,'$2y$10$tOIRZA3jWLEJsJr4pu0nIOOnftqLMI6O3pmXxSAfFIDduH5FrB0jG','cliente',NULL,'2996255976',NULL,NULL,'2026-03-17 18:05:25','2026-03-17 18:05:25'),(7,'Camp Barbara','camp.barbara@artesanos.com',NULL,'$2y$10$/aIVTqqJXq/RLrsgj5mW2uB1VbSAcqv0EEuRBEp0d.OeXGDhsXvJC','cliente',NULL,'2995896300',NULL,NULL,'2026-03-17 18:05:26','2026-03-17 18:05:26'),(8,'Cofre Nelly','cofre.nelly@artesanos.com',NULL,'$2y$10$Yrrh0w9Ad3f3xUb1N2Qss.pfnDSJXwj1fohWMXrRSFMdV6aQqt8a6','cliente',NULL,'2995367997',NULL,NULL,'2026-03-17 18:05:26','2026-03-17 18:05:26'),(9,'Contreras Sandra','contreras.sandra@artesanos.com',NULL,'$2y$10$8v9kYjsVBI5acEvOKS.u/ebUnPMKZCD/XXL7KDADW7Frm0LqA7DnK','cliente',NULL,'2995347972',NULL,NULL,'2026-03-17 18:05:26','2026-03-17 18:05:26'),(10,'Fernandez Elsa','fernandez.elsa@artesanos.com',NULL,'$2y$10$WATC/nLyNUlVE8YT8AKQ.OtS64QfLCY6ooKPmIZ6nCFwxoSTXAgNW','cliente',NULL,'2995335930',NULL,NULL,'2026-03-17 18:05:26','2026-03-17 18:05:26'),(11,'Gatas Veronica','gatas.veronica@artesanos.com',NULL,'$2y$10$m1DGc.L0xXl8vMEciBUcj.uvxV04TW8ksOa2JI44./DJjMilbn1jq','cliente',NULL,'2994159349',NULL,NULL,'2026-03-17 18:05:26','2026-03-17 18:05:26'),(12,'Morales Florencia','morales.florencia@artesanos.com',NULL,'$2y$10$e02bLQGWTfGS/cNZlxfuWebrB8n2K/MsqRHJcdtKC7py5nFI8tLBy','cliente',NULL,'2995948456',NULL,NULL,'2026-03-17 18:05:26','2026-03-17 18:05:26'),(13,'Ojeda Margarita','ojeda.margarita@artesanos.com',NULL,'$2y$10$Aw1Emnl3yp32OCgVGukx7OqF3y/FLFbTaIKZazSLvl8ad2DKlU0dm','cliente',NULL,'2995702625',NULL,NULL,'2026-03-17 18:05:26','2026-03-17 18:05:26'),(14,'Ojeda Yanina','ojeda.yanina@artesanos.com',NULL,'$2y$10$WSNPuP7o0RVgdM4KvQmaxubdgAgR9U5J7GcWzHWLnMqba52USUGWC','cliente',NULL,'2994026088',NULL,NULL,'2026-03-17 18:05:26','2026-03-17 18:05:26'),(15,'Penalba Ramiro','penalba.ramiro@artesanos.com',NULL,'$2y$10$E5wQ1t0Lv75Qzjzx.ZsAeOALiW8R1FFKpRqCvc8fvESi5IFmProfW','cliente',NULL,'2995099832',NULL,NULL,'2026-03-17 18:05:26','2026-03-17 18:05:26'),(16,'Peralta Silvia','peralta.silvia@artesanos.com',NULL,'$2y$10$yw7tNwiQl3ff.vrXKs1ss.p.p5N2b2YDHxkBKrqI0fAg3ku/4sHIW','cliente',NULL,'2994069933',NULL,NULL,'2026-03-17 18:05:26','2026-03-17 18:05:26'),(17,'Quiroga Adriana','quiroga.adriana@artesanos.com',NULL,'$2y$10$.dD2C2g6QGW82VnV7UHWTuO74ZSDZmPgu2EgnDFS1VLtYypM5Nz6m','cliente',NULL,'2995306321',NULL,NULL,'2026-03-17 18:05:26','2026-03-17 18:05:26'),(18,'Rotta Brenda','rotta.brenda@artesanos.com',NULL,'$2y$10$4uwiclNNTF9M7adg5.JSPupzb4wNz7ZdhBBtYyxUkfTzhSMrGP5Zi','cliente',NULL,'2994114908',NULL,NULL,'2026-03-17 18:05:26','2026-03-17 18:05:26'),(19,'Sosa Daniela','sosa.daniela@artesanos.com',NULL,'$2y$10$a0H.S0P.jhF96LAgw/JSfuH0zHRUxqv19HTTDvlYDKjMh7jVbvBQe','cliente',NULL,'2994835611',NULL,NULL,'2026-03-17 18:05:26','2026-03-17 18:05:26');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-03-18  9:13:02
