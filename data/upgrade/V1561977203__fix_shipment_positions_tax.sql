ALTER TABLE `ecommerce_shipment_positions` DROP `tax_type`;
ALTER TABLE `ecommerce_shipment_positions` CHANGE `tax_rate` `amount_rate` INT(5)  UNSIGNED  NOT NULL;
ALTER TABLE `ecommerce_shipment_positions` MODIFY COLUMN `amount_rate` INT(5) UNSIGNED NOT NULL AFTER `amount_type`;
