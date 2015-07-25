-- Create syntax for TABLE 'ecommerce_cart_positions'
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

-- Create syntax for TABLE 'ecommerce_carts'
CREATE TABLE `ecommerce_carts` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'open',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `status` (`status`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Create syntax for TABLE 'ecommerce_orders'
CREATE TABLE `ecommerce_orders` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(200) NOT NULL,
  `user_id` int(11) unsigned NOT NULL,
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
  KEY `billing_invoice_id` (`billing_invoice_id`),
  KEY `billing_address_id` (`billing_address_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Create syntax for TABLE 'ecommerce_product_attributes'
CREATE TABLE `ecommerce_product_attributes` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ecommerce_product_id` int(11) unsigned NOT NULL,
  `key` varchar(250) NOT NULL DEFAULT '',
  `value` varchar(250) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_key` (`ecommerce_product_id`,`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Create syntax for TABLE 'ecommerce_product_groups'
CREATE TABLE `ecommerce_product_groups` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL DEFAULT '',
  `description` text,
  `tags` varchar(250) DEFAULT NULL,
  `is_published` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `is_promoted` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `access` varchar(250) NOT NULL DEFAULT 'any',
  `cover_media_id` int(11) unsigned DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `is_published` (`is_published`),
  KEY `cover_media_id` (`cover_media_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Create syntax for TABLE 'ecommerce_product_prices'
CREATE TABLE `ecommerce_product_prices` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ecommerce_product_id` int(11) unsigned NOT NULL,
  `group` varchar(20) NOT NULL,
  `tax_type` varchar(20) NOT NULL DEFAULT '',
  `amount_currency` char(3) NOT NULL DEFAULT 'EUR',
  `amount_type` char(5) NOT NULL DEFAULT 'net',
  `amount_rate` int(5) unsigned NOT NULL DEFAULT '0',
  `amount` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ecommerce_product_id` (`ecommerce_product_id`)
) ENGINE=InnoDB CHARSET=utf8 COMMENT='no fixed tax_rate';

-- Create syntax for TABLE 'ecommerce_products'
CREATE TABLE `ecommerce_products` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ecommerce_product_group_id` int(11) unsigned DEFAULT NULL,
  `number` varchar(100) NOT NULL DEFAULT '',
  `title` varchar(250) NOT NULL DEFAULT '',
  `description` text,
  `cover_media_id` int(11) unsigned DEFAULT NULL,
  `stock` int(10) NOT NULL DEFAULT '0',
  `stock_reserved` int(10) NOT NULL DEFAULT '0',
  `stock_target` int(10) NOT NULL DEFAULT '0',
  `is_published` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `number` (`number`),
  KEY `is_published` (`is_published`),
  KEY `ecommerce_product_group_id` (`ecommerce_product_group_id`),
  KEY `cover_media_id` (`cover_media_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Create syntax for TABLE 'ecommerce_shipment_positions'
CREATE TABLE `ecommerce_shipment_positions` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ecommerce_shipment_id` int(11) unsigned DEFAULT NULL COMMENT 'NULL until assigned to shipment',
  `description` varchar(250) NOT NULL,
  `quantity` decimal(10,2) unsigned NOT NULL DEFAULT '1.00',
  `tax_type` varchar(20) NOT NULL DEFAULT '',
  `tax_rate` int(5) unsigned NOT NULL,
  `amount_currency` char(3) NOT NULL DEFAULT 'EUR',
  `amount_type` char(5) NOT NULL DEFAULT 'net' COMMENT 'will always be net',
  `amount` int(10) NOT NULL COMMENT 'the net value of the item',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `billing_invoice_id` (`ecommerce_shipment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Create syntax for TABLE 'ecommerce_shipments'
CREATE TABLE `ecommerce_shipments` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'created',
  `number` varchar(200) DEFAULT NULL,
  `tracking` varchar(200) DEFAULT NULL,
  `method` varchar(100) NOT NULL DEFAULT '',
  `address_recipient` varchar(250) DEFAULT '',
  `address_organization` varchar(250) DEFAULT NULL,
  `address_address_line_1` varchar(250) DEFAULT NULL,
  `address_address_line_2` varchar(250) DEFAULT NULL,
  `address_locality` varchar(100) DEFAULT NULL,
  `address_dependent_locality` varchar(100) DEFAULT NULL,
  `address_postal_code` varchar(100) DEFAULT NULL,
  `address_sorting_code` varchar(100) DEFAULT NULL,
  `address_country` char(2) DEFAULT 'DE',
  `address_administrative_area` varchar(200) DEFAULT NULL,
  `address_phone` varchar(200) DEFAULT NULL,
  `terms` text,
  `note` text,
  `created` datetime NOT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `number` (`number`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Augment other tables
ALTER TABLE `users` ADD `has_accepted_terms` TINYINT(1)  UNSIGNED  NOT NULL  DEFAULT '0'  COMMENT 'ecommerce' AFTER `is_notified`;
ALTER TABLE `users` ADD `shipping_address_id` int(11) unsigned DEFAULT NULL COMMENT 'ecommerce' AFTER `billing_address_id`;

