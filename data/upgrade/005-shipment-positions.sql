ALTER TABLE `ecommerce_shipments` ADD `tax_rate` INT(4)  UNSIGNED  NOT NULL  AFTER `method`;
ALTER TABLE `ecommerce_shipments` ADD `tax_note` VARCHAR(250)  NOT NULL  DEFAULT ''  AFTER `tax_rate`;

CREATE TABLE `ecommerce_shipment_positions` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ecommerce_shipment_id` int(11) unsigned DEFAULT NULL COMMENT 'NULL until assigned to shipment',
  `description` varchar(250) NOT NULL,
  `quantity` decimal(10,2) unsigned NOT NULL DEFAULT '1.00',
  `amount_currency` char(3) NOT NULL DEFAULT 'EUR',
  `amount_type` char(5) NOT NULL DEFAULT 'net',
  `amount` int(10) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `billing_invoice_id` (`ecommerce_shipment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
