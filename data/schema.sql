# ************************************************************
# Sequel Pro SQL dump
# Version 4096
#
# http://www.sequelpro.com/
# http://code.google.com/p/sequel-pro/
#
# Host: localhost (MySQL 10.0.10-MariaDB-log)
# Datenbank: rainmap
# Erstellungsdauer: 2014-06-03 14:11:01 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Export von Tabelle ecommerce_cart_positions
# ------------------------------------------------------------

DROP TABLE IF EXISTS `ecommerce_cart_positions`;

CREATE TABLE `ecommerce_cart_positions` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ecommerce_cart_id` int(11) unsigned NOT NULL,
  `ecommerce_product_id` int(11) unsigned NOT NULL,
  `quantity` int(10) unsigned NOT NULL DEFAULT '0',
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ecommerce_cart_id` (`ecommerce_cart_id`),
  KEY `ecommerce_product_id` (`ecommerce_product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Export von Tabelle ecommerce_carts
# ------------------------------------------------------------

DROP TABLE IF EXISTS `ecommerce_carts`;

CREATE TABLE `ecommerce_carts` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned DEFAULT NULL,
  `virtual_user_id` int(11) unsigned DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'open',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `status` (`status`),
  KEY `user_id` (`user_id`),
  KEY `virtual_user_id` (`virtual_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Export von Tabelle ecommerce_orders
# ------------------------------------------------------------

DROP TABLE IF EXISTS `ecommerce_orders`;

CREATE TABLE `ecommerce_orders` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(200) NOT NULL,
  `user_id` int(11) unsigned DEFAULT NULL,
  `virtual_user_id` int(11) unsigned DEFAULT NULL,
  `user_session_id` varchar(250) NOT NULL,
  `number` varchar(100) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'created',
  `ecommerce_cart_id` int(11) NOT NULL,
  `ecommerce_shipment_id` int(11) unsigned DEFAULT NULL,
  `billing_invoice_id` int(11) unsigned DEFAULT NULL,
  `shipping_method` varchar(100) NOT NULL,
  `shipping_address_id` int(11) unsigned DEFAULT NULL,
  `payment_method` varchar(100) NOT NULL DEFAULT '',
  `billing_address_id` int(11) unsigned DEFAULT NULL,
  `user_note` text,
  `internal_note` text,
  `has_accepted_terms` tinyint(1) unsigned DEFAULT '0',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `number` (`number`),
  KEY `ecommerce_cart_id` (`ecommerce_cart_id`),
  KEY `ecommerce_shipment_id` (`ecommerce_shipment_id`),
  KEY `user_id` (`user_id`),
  KEY `virtual_user_id` (`virtual_user_id`),
  KEY `billing_invoice_id` (`billing_invoice_id`),
  KEY `billing_address_id` (`billing_address_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Export von Tabelle ecommerce_product_groups
# ------------------------------------------------------------

DROP TABLE IF EXISTS `ecommerce_product_groups`;

CREATE TABLE `ecommerce_product_groups` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL DEFAULT '',
  `description` text,
  `is_published` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `access` varchar(250) NOT NULL DEFAULT 'any',
  `cover_media_id` int(11) unsigned DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `is_published` (`is_published`),
  KEY `cover_media_id` (`cover_media_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Export von Tabelle ecommerce_product_prices
# ------------------------------------------------------------

DROP TABLE IF EXISTS `ecommerce_product_prices`;

CREATE TABLE `ecommerce_product_prices` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ecommerce_product_id` int(11) unsigned NOT NULL,
  `group` varchar(20) NOT NULL,
  `price_currency` char(3) NOT NULL DEFAULT 'EUR',
  `price_type` char(5) NOT NULL DEFAULT 'net',
  `price` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ecommerce_product_id` (`ecommerce_product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Export von Tabelle ecommerce_products
# ------------------------------------------------------------

DROP TABLE IF EXISTS `ecommerce_products`;

CREATE TABLE `ecommerce_products` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ecommerce_product_group_id` int(11) unsigned DEFAULT NULL,
  `number` varchar(100) NOT NULL DEFAULT '',
  `title` varchar(250) NOT NULL DEFAULT '',
  `description` text,
  `cover_media_id` int(11) unsigned DEFAULT NULL,
  `stock` int(10) NOT NULL DEFAULT '0',
  `is_published` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `number` (`number`),
  KEY `is_published` (`is_published`),
  KEY `ecommerce_product_group_id` (`ecommerce_product_group_id`),
  KEY `cover_media_id` (`cover_media_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Export von Tabelle ecommerce_shipments
# ------------------------------------------------------------

DROP TABLE IF EXISTS `ecommerce_shipments`;

CREATE TABLE `ecommerce_shipments` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `status` varchar(20) NOT NULL DEFAULT 'created',
  `number` varchar(200) DEFAULT NULL,
  `tracking` varchar(200) DEFAULT NULL,
  `method` varchar(100) NOT NULL DEFAULT '',
  `address_name` varchar(250) DEFAULT '',
  `address_company` varchar(250) DEFAULT NULL,
  `address_street` varchar(250) DEFAULT NULL,
  `address_city` varchar(100) DEFAULT NULL,
  `address_zip` varchar(100) DEFAULT NULL,
  `address_country` char(2) DEFAULT 'DE',
  `address_phone` varchar(200) DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `number` (`number`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;




/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
