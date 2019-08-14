<?php

$installer = $this;

$installer->startSetup();

$date = date('Y-m-d H:i:s', time()-3600*24*14); // two previous weeks
$quote = $this->getTable('sales/quote');

$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('emark_cartalert')};
CREATE TABLE {$this->getTable('emark_cartalert')} (
  `cartalert_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `store_id` smallint(5) unsigned NOT NULL,
  `customer_id` int(10) unsigned NOT NULL,
  `quote_id` int(10) unsigned NOT NULL,
  `is_preprocessed` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `status` enum('pending','invalid') NOT NULL DEFAULT 'pending',
  `abandoned_at` datetime DEFAULT NULL,
  `sheduled_at` datetime NOT NULL,
  `follow_up` enum('first','second','third') NOT NULL DEFAULT 'first',
  `customer_email` varchar(255) NOT NULL,
  `customer_fname` varchar(255) NOT NULL,
  `customer_lname` varchar(255) NOT NULL,
  `products` text NOT NULL,
  PRIMARY KEY (`cartalert_id`),
  KEY `customer_email` (`customer_email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO {$this->getTable('core/config_data')} (`scope` , `scope_id` , `path` , `value` )
    VALUES ('default', '0', 'catalog/emark_cartalert/from_date', '$date');
	
-- DROP TABLE IF EXISTS {$this->getTable('emark_cartalert_history')};
CREATE TABLE {$this->getTable('emark_cartalert_history')} (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `quote_id` int(10) unsigned NOT NULL,
  `customer_id` int(10) unsigned NOT NULL,
  `recover_code` char(32) NOT NULL,
  `sent_at` datetime NOT NULL,
  `recovered_from` varchar(32) NOT NULL,
  `recovered_at` datetime DEFAULT NULL,
  `follow_up` enum('first','second','third') NOT NULL DEFAULT 'first',
  `customer_name` varchar(255) NOT NULL,
  `customer_email` varchar(255) NOT NULL,
  `txt` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `$quote` ADD `allow_alerts` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1' ;

");

$installer->getConnection()->addColumn(
	$quote,
	'allow_alerts',
	'TINYINT(1) UNSIGNED NOT NULL DEFAULT 1'
);

$installer->endSetup(); 
