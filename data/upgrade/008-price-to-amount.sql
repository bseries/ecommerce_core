ALTER TABLE `ecommerce_product_prices` CHANGE `price_currency` `amount_currency` CHAR(3)  NOT NULL  DEFAULT 'EUR';
ALTER TABLE `ecommerce_product_prices` CHANGE `price_type` `amount_type` CHAR(5)  NOT NULL  DEFAULT 'net';
ALTER TABLE `ecommerce_product_prices` CHANGE `price` `amount` INT(10)  UNSIGNED  NOT NULL;

