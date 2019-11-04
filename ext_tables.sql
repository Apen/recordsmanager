#
# Table structure for table 'tx_recordsmanager_config'
#
CREATE TABLE tx_recordsmanager_config (
  uid int(11) NOT NULL auto_increment,
  pid int(11) DEFAULT '0' NOT NULL,
  tstamp int(11) DEFAULT '0' NOT NULL,
  crdate int(11) DEFAULT '0' NOT NULL,
  cruser_id int(11) DEFAULT '0' NOT NULL,
  deleted tinyint(4) DEFAULT '0' NOT NULL,
  hidden tinyint(4) DEFAULT '0' NOT NULL,
  sorting int(11) unsigned DEFAULT '0' NOT NULL,
  title tinytext,
  type tinytext,
  sqltable tinytext,
  sqlfields text,
  extrawhere text,
  extragroupby tinytext,
  extraorderby tinytext,
  extralimit tinytext,
  exportmode tinytext,
  eidkey tinytext,
  excludefields tinytext,
	exportfilterfield text,
	extrats text,
	hidefields tinytext,
	dateformat tinyint(4) DEFAULT '0' NOT NULL,
	sqlfieldsinsert text,
	permsgroup text,
	insertdefaultpid tinytext,
	authlogin tinytext,
	authpassword tinytext,
  PRIMARY KEY (uid),
  KEY parent (pid)
);