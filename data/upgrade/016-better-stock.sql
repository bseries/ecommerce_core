ALTER TABLE `ecommerce_products` ADD `stock_reserved` INT(10)  NOT NULL  DEFAULT '0'  AFTER `stock`;
ALTER TABLE `ecommerce_products` ADD `stock_remote` INT(10)  NOT NULL  DEFAULT '0'  AFTER `stock_reserved`;

CREATE TABLE `ecommerce_product_stocks` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ecommerce_product_id` int(11) unsigned NOT NULL,
  `model` varchar(50) NOT NULL,
  `foreign_key` int(11) unsigned NOT NULL,
  `type` varchar(20) NOT NULL DEFAULT '',
  `operation` varchar(20) NOT NULL DEFAULT '' COMMENT 'take, put, reserve, free',
  `quantity` int(10) unsigned NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ecommerce_product_id` (`ecommerce_product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;