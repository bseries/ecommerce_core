ALTER TABLE `ecommerce_products` ADD `stock_reserved` INT(10)  NOT NULL  DEFAULT '0'  AFTER `stock`;
ALTER TABLE `ecommerce_products` ADD `stock_target` INT(10)  NOT NULL  DEFAULT '0'  AFTER `stock_reserved`;


