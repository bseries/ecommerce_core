ALTER TABLE `ecommerce_product_prices` DROP `tax_type`;
ALTER TABLE `ecommerce_product_prices` MODIFY COLUMN `amount` INT(10) UNSIGNED NOT NULL AFTER `group`;


