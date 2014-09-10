ALTER TABLE `ecommerce_product_prices` ADD `tax` VARCHAR(20)  NOT NULL  DEFAULT ''  AFTER `group`;

ALTER TABLE `ecommerce_shipment_positions` ADD `tax` VARCHAR(20)   NOT NULL  AFTER `quantity`;
ALTER TABLE `ecommerce_shipment_positions` ADD `tax_rate` INT(5)  UNSIGNED  NOT NULL  DEFAULT '0'  AFTER `tax`;

