<?php
return [
    'auto_increment_test' => 'CREATE TABLE `auto_increment_test` (
  `int_auto_increment_with_nullable` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `int_disabled_auto_increment` smallint(12) unsigned DEFAULT \'0\',
  UNIQUE KEY `AUTO_INCREMENT_TEST_INT_AUTO_INCREMENT_WITH_NULLABLE` (`int_auto_increment_with_nullable`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8',
    'reference_table' => 'CREATE TABLE `reference_table` (
  `tinyint_ref` tinyint(7) NOT NULL AUTO_INCREMENT,
  `tinyint_without_padding` tinyint(2) NOT NULL DEFAULT \'0\',
  `bigint_without_padding` bigint(20) NOT NULL DEFAULT \'0\',
  `smallint_without_padding` smallint(5) NOT NULL DEFAULT \'0\',
  `integer_without_padding` int(11) NOT NULL DEFAULT \'0\',
  `smallint_with_big_padding` smallint(254) NOT NULL DEFAULT \'0\',
  `smallint_without_default` smallint(2) DEFAULT NULL,
  `int_without_unsigned` int(2) DEFAULT NULL,
  `int_unsigned` int(2) unsigned DEFAULT NULL,
  `bigint_default_nullable` bigint(2) unsigned DEFAULT \'1\',
  `bigint_not_default_not_nullable` bigint(2) unsigned NOT NULL,
  `smallint_ref` smallint(254) NOT NULL DEFAULT \'0\',
  PRIMARY KEY (`tinyint_ref`,`smallint_ref`),
  UNIQUE KEY `REFERENCE_TABLE_SMALLINT_REF` (`smallint_ref`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8',
    'test_table' => 'CREATE TABLE `test_table` (
  `smallint` smallint(3) DEFAULT NULL,
  `tinyint` tinyint(7) DEFAULT NULL,
  `bigint` bigint(13) DEFAULT \'0\',
  `float` float(12,10) DEFAULT \'0.0000000000\',
  `double` double(245,10) DEFAULT \'11111111.1111110000\',
  `decimal` decimal(15,4) DEFAULT \'0.0000\',
  `date` date DEFAULT NULL,
  `timestamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `datetime` datetime DEFAULT \'0000-00-00 00:00:00\',
  `longtext` longtext,
  `mediumtext` mediumtext,
  `varchar` varchar(254) DEFAULT NULL,
  `mediumblob` mediumblob,
  `blob` blob,
  `boolean` tinyint(1) DEFAULT NULL,
  `integer_main` int(12) unsigned DEFAULT NULL,
  `smallint_main` smallint(254) NOT NULL DEFAULT \'0\',
  UNIQUE KEY `TEST_TABLE_SMALLINT_FLOAT` (`smallint`,`float`),
  UNIQUE KEY `TEST_TABLE_DOUBLE` (`double`),
  KEY `TEST_TABLE_TINYINT_BIGINT` (`tinyint`,`bigint`),
  KEY `TEST_TABLE_SMALLINT_MAIN_REFERENCE_TABLE_SMALLINT_REF` (`smallint_main`),
  KEY `FK_FB77604C299EB8612D01E4AF8D9931F2` (`integer_main`),
  CONSTRAINT `FK_FB77604C299EB8612D01E4AF8D9931F2` FOREIGN KEY (`integer_main`) 
REFERENCES `auto_increment_test` (`int_auto_increment_with_nullable`) ON DELETE CASCADE,
  CONSTRAINT `TEST_TABLE_SMALLINT_MAIN_REFERENCE_TABLE_SMALLINT_REF` FOREIGN KEY (`smallint_main`) 
REFERENCES `reference_table` (`smallint_ref`) ON DELETE CASCADE,
  CONSTRAINT `TEST_TABLE_TINYINT_REFERENCE_TABLE_TINYINT_REF` FOREIGN KEY (`tinyint`) 
REFERENCES `reference_table` (`tinyint_ref`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8',
];
