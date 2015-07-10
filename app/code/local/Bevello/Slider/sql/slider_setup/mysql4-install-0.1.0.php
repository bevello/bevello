<?php
/**
 * Created by PhpStorm.
 * User: mcoffey
 * Date: 5/28/15
 * Time: 12:16 PM
 */

$installer = $this;

$installer->startSetup();

$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('slider')};
CREATE TABLE {$this->getTable('slider')} (
  `slider_id` int(11) unsigned NOT NULL auto_increment,
  `title` varchar(255) NOT NULL default '',
  `link` varchar(255) NOT NULL default '',
  `image` varchar(255) NOT NULL default '',
  `alt` varchar(255) NOT NULL default '',
  `event_category` varchar(255) NOT NULL default '',
  `event_label` varchar(255) NOT NULL default '',
  `status` smallint(6) NOT NULL default '0',
  PRIMARY KEY (`slider_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");

$installer->endSetup();