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
  ALTER TABLE {$this->getTable('bevello_footerads')}
  ADD `rowId` int(11) unsigned NOT NULL AFTER `status`;
    ");

$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('bevello_footerrow')};
CREATE TABLE {$this->getTable('bevello_footerrow')} (
  `rowId` int(11) unsigned NOT NULL auto_increment,
  `title` varchar(255) NOT NULL default '',
  `type` varchar(255) NOT NULL default '',
  `status` smallint(6) NOT NULL default '0',
  PRIMARY KEY (`rowId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");

$installer->endSetup();