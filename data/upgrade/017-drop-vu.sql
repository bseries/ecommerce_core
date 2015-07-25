-- After migration command.
-- ALTER TABLE `ecommerce_carts` DROP `virtual_user_id`;
-- ALTER TABLE `ecommerce_carts` CHANGE `user_id` `user_id` INT(11)  UNSIGNED  NOT NULL;
-- ALTER TABLE `ecommerce_orders` DROP `virtual_user_id`;
-- ALTER TABLE `ecommerce_orders` CHANGE `user_id` `user_id` INT(11)  UNSIGNED  NOT NULL;
-- ALTER TABLE `ecommerce_shipments` DROP `virtual_user_id`;
-- ALTER TABLE `ecommerce_shipments` CHANGE `user_id` `user_id` INT(11)  UNSIGNED  NOT NULL;

