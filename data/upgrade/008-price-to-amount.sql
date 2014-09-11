ALTER TABLE `ecommerce_product_prices` CHANGE `price_currency` `amount_currency` CHAR(3)  CHARACTER SET utf8  COLLATE utf8_general_ci  NOT NULL  DEFAULT 'EUR';
ALTER TABLE `ecommerce_product_prices` CHANGE `price_type` `amount_type` CHAR(5)  CHARACTER SET utf8  COLLATE utf8_general_ci  NOT NULL  DEFAULT 'net';
ALTER TABLE `ecommerce_product_prices` CHANGE `price` `amount` INT(10)  UNSIGNED  NOT NULL;

