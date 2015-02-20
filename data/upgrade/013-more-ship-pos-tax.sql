ALTER TABLE `ecommerce_shipment_positions` DROP `tax_type`;
ALTER TABLE `ecommerce_shipment_positions` CHANGE `tax_rate` `amount_rate` INT(5)  UNSIGNED  NOT NULL;
ALTER TABLE `ecommerce_shipment_positions` MODIFY COLUMN `amount` INT(10) NOT NULL AFTER `quantity`;
ALTER TABLE `ecommerce_shipment_positions` MODIFY COLUMN `amount_currency` CHAR(3) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'EUR' AFTER `amount`;
ALTER TABLE `ecommerce_shipment_positions` MODIFY COLUMN `amount_type` CHAR(5) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'net' AFTER `amount_currency`;

