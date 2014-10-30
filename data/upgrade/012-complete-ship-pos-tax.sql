ALTER TABLE `ecommerce_shipment_positions` ADD `tax_type` VARCHAR(20)  NOT NULL  DEFAULT ''  AFTER `quantity`;
ALTER TABLE `ecommerce_shipment_positions` ADD `tax_rate` INT(5)  UNSIGNED  NOT NULL  AFTER `tax_type`;

