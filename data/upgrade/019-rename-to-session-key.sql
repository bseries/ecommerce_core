ALTER TABLE `ecommerce_orders` CHANGE `user_session_id` `user_session_key` VARCHAR(250)  CHARACTER SET utf8  COLLATE utf8_general_ci  NOT NULL  DEFAULT '';
