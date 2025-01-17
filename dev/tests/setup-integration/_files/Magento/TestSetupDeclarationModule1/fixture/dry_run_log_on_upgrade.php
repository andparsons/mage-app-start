<?php
// @codingStandardsIgnoreFile
return ['ALTER TABLE `reference_table` MODIFY COLUMN `tinyint_without_padding` tinyint(2)  NOT NULL   , MODIFY COLUMN `bigint_default_nullable` bigint(2) UNSIGNED NULL DEFAULT 123  , MODIFY COLUMN `bigint_not_default_not_nullable` bigint(20)  NOT NULL   

ALTER TABLE `auto_increment_test` MODIFY COLUMN `int_auto_increment_with_nullable` int(15) UNSIGNED NULL   

ALTER TABLE `test_table` MODIFY COLUMN `float` float(12, 10)  NULL DEFAULT 0 , MODIFY COLUMN `double` double(245, 10)  NULL  , MODIFY COLUMN `timestamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP  , MODIFY COLUMN `varchar` varchar(100) NULL  , MODIFY COLUMN `boolean` BOOLEAN NULL DEFAULT 1 

'];
