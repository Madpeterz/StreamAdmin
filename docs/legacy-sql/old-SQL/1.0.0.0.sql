UPDATE `slconfig` SET `db_version` = '1.0.0.1' WHERE `slconfig`.`id` = 1;
ALTER TABLE `slconfig` ADD `clients_list_mode` TINYINT(1) NOT NULL DEFAULT '0' AFTER `sllinkcode`;
