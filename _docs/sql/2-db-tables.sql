SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

USE `symfony_ecommerce`;

SET NAMES utf8mb4;

DROP TABLE IF EXISTS `address_type`;
CREATE TABLE `address_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(64) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `cart`;
CREATE TABLE `cart` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_BA388B7A76ED395` (`user_id`),
  CONSTRAINT `FK_BA388B7A76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `cart_product_quantity`;
CREATE TABLE `cart_product_quantity` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `cart_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_E987F83B4584665A` (`product_id`),
  KEY `IDX_E987F83B1AD5CDBF` (`cart_id`),
  CONSTRAINT `FK_E987F83B1AD5CDBF` FOREIGN KEY (`cart_id`) REFERENCES `cart` (`id`),
  CONSTRAINT `FK_E987F83B4584665A` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `category`;
CREATE TABLE `category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `media_id` int(11) DEFAULT NULL,
  `name` varchar(128) NOT NULL,
  `description` varchar(512) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_64C19C1EA9FDD75` (`media_id`),
  CONSTRAINT `FK_64C19C1EA9FDD75` FOREIGN KEY (`media_id`) REFERENCES `media` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `customer_address`;
CREATE TABLE `customer_address` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `last_name` varchar(128) NOT NULL,
  `first_name` varchar(128) NOT NULL,
  `phone` varchar(12) NOT NULL,
  `address` varchar(512) NOT NULL,
  `postal_code` varchar(7) NOT NULL,
  `city` varchar(256) NOT NULL,
  `country` varchar(64) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_1193CB3FC54C8C93` (`type_id`),
  KEY `IDX_1193CB3FA76ED395` (`user_id`),
  CONSTRAINT `FK_1193CB3FA76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`),
  CONSTRAINT `FK_1193CB3FC54C8C93` FOREIGN KEY (`type_id`) REFERENCES `address_type` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `media`;
CREATE TABLE `media` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) DEFAULT NULL,
  `path` varchar(256) NOT NULL,
  `alt` varchar(256) DEFAULT NULL,
  `type` varchar(256) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_6A2CA10C4584665A` (`product_id`),
  CONSTRAINT `FK_6A2CA10C4584665A` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `order_product_quantity`;
CREATE TABLE `order_product_quantity` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `original_order_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_365568BD4584665A` (`product_id`),
  KEY `IDX_365568BDAEC08A29` (`original_order_id`),
  CONSTRAINT `FK_365568BD4584665A` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`),
  CONSTRAINT `FK_365568BDAEC08A29` FOREIGN KEY (`original_order_id`) REFERENCES `user_order` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `product`;
CREATE TABLE `product` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `name` varchar(256) NOT NULL,
  `description` varchar(512) DEFAULT NULL,
  `price` double NOT NULL,
  `available` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `IDX_D34A04AD12469DE2` (`category_id`),
  CONSTRAINT `FK_D34A04AD12469DE2` FOREIGN KEY (`category_id`) REFERENCES `category` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `last_name` varchar(128) NOT NULL,
  `first_name` varchar(128) NOT NULL,
  `phone` varchar(12) NOT NULL,
  `email` varchar(180) NOT NULL,
  `password` varchar(255) NOT NULL,
  `is_verified` tinyint(1) NOT NULL DEFAULT 0,
  `roles` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT '(DC2Type:json)' CHECK (json_valid(`roles`)),
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_8D93D649E7927C74` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `user_order`;
CREATE TABLE `user_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `customer_address_id` int(11) NOT NULL,
  `number` int(11) NOT NULL,
  `is_validated` tinyint(1) NOT NULL DEFAULT 0,
  `order_date` datetime NOT NULL,
  `delivery_fee` decimal(6,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_17EB68C0A76ED395` (`user_id`),
  KEY `IDX_17EB68C087EABF7` (`customer_address_id`),
  CONSTRAINT `FK_17EB68C087EABF7` FOREIGN KEY (`customer_address_id`) REFERENCES `customer_address` (`id`),
  CONSTRAINT `FK_17EB68C0A76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
