ALTER TABLE `ecommerce_product_groups` ADD `site` VARCHAR(50)  NULL  DEFAULT NULL  AFTER `id`;
ALTER TABLE `ecommerce_shipments` ADD `site` VARCHAR(50)  NULL  DEFAULT NULL  AFTER `id`;
