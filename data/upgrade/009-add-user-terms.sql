ALTER TABLE `users` ADD `has_accepted_terms` TINYINT(1)  UNSIGNED  NOT NULL  DEFAULT '0'  COMMENT 'ecommerce_core' AFTER `is_notified`;
ALTER TABLE `virtual_users` ADD `has_accepted_terms` TINYINT(1)  UNSIGNED  NOT NULL  DEFAULT '0'  COMMENT 'ecommerce_core' AFTER `is_notified`;

