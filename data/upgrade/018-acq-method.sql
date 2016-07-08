ALTER TABLE `ecommerce_product_prices` ADD `method` VARCHAR(100)  NOT NULL  DEFAULT ''  AFTER `amount`;
ALTER TABLE `ecommerce_cart_positions` ADD `method` VARCHAR(50)  NOT NULL  DEFAULT ''  AFTER `quantity`;
