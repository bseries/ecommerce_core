CREATE TABLE `ecommerce_product_attributes` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ecommerce_product_id` int(11) unsigned NOT NULL,
  `key` varchar(250) NOT NULL DEFAULT '',
  `value` varchar(250) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_key` (`ecommerce_product_id`,`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
