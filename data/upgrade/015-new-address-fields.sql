ALTER TABLE `ecommerce_shipments` CHANGE `address_name` `address_recipient` VARCHAR(250)  CHARACTER SET utf8  COLLATE utf8_general_ci  NULL  DEFAULT '';
ALTER TABLE `ecommerce_shipments` CHANGE `address_company` `address_organization` VARCHAR(250)  CHARACTER SET utf8  COLLATE utf8_general_ci  NULL  DEFAULT NULL;
ALTER TABLE `ecommerce_shipments` CHANGE `address_street` `address_address_line_1` VARCHAR(250)  CHARACTER SET utf8  COLLATE utf8_general_ci  NULL  DEFAULT NULL;
ALTER TABLE `ecommerce_shipments` CHANGE `address_city` `address_locality` VARCHAR(100)  CHARACTER SET utf8  COLLATE utf8_general_ci  NULL  DEFAULT NULL;
ALTER TABLE `ecommerce_shipments` CHANGE `address_zip` `address_postal_code` VARCHAR(100)  CHARACTER SET utf8  COLLATE utf8_general_ci  NULL  DEFAULT NULL;
ALTER TABLE `ecommerce_shipments` ADD `address_address_line_2` VARCHAR(250)  NULL  DEFAULT NULL  AFTER `address_address_line_1`;
ALTER TABLE `ecommerce_shipments` ADD `address_dependent_locality` VARCHAR(100)  NULL  DEFAULT NULL  AFTER `address_locality`;
ALTER TABLE `ecommerce_shipments` ADD `address_sorting_code` VARCHAR(100)  NULL  DEFAULT NULL  AFTER `address_postal_code`;
ALTER TABLE `ecommerce_shipments` ADD `address_administrative_area` VARCHAR(200)  NULL  DEFAULT NULL  AFTER `address_country`;


