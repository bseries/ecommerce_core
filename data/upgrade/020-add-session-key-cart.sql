ALTER TABLE `ecommerce_carts` ADD `user_session_key` VARCHAR(250)  NULL  DEFAULT NULL  AFTER `user_id`;
ALTER TABLE `ecommerce_carts` CHANGE `user_id` `user_id` INT(11)  UNSIGNED  NULL;
ALTER TABLE `ecommerce_orders` CHANGE `user_id` `user_id` INT(11)  UNSIGNED  NULL;
ALTER TABLE `ecommerce_orders` CHANGE `user_session_key` `user_session_key` VARCHAR(250)  CHARACTER SET utf8  COLLATE utf8_general_ci  NULL  DEFAULT '';
