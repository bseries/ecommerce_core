ALTER TABLE `users` CHANGE `shipping_address_id` `shipping_address_id` INT(11)  UNSIGNED  NULL  DEFAULT NULL  COMMENT 'ecommerce_core';
ALTER TABLE `users` CHANGE `has_accepted_terms` `has_accepted_terms` TINYINT(1)  UNSIGNED  NOT NULL  DEFAULT '0'  COMMENT 'ecommerce_core';

ALTER TABLE `virtual_users` CHANGE `shipping_address_id` `shipping_address_id` INT(11)  UNSIGNED  NULL  DEFAULT NULL  COMMENT 'ecommerce_core';
ALTER TABLE `virtual_users` CHANGE `has_accepted_terms` `has_accepted_terms` TINYINT(1)  UNSIGNED  NOT NULL  DEFAULT '0'  COMMENT 'ecommerce_core';

