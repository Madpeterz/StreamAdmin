ALTER TABLE `slconfig` ADD `datatable_itemsperpage` INT(3) NOT NULL DEFAULT '10' AFTER `eventstorage`;
UPDATE `slconfig` SET `db_version` = '1.0.0.5' WHERE `slconfig`.`id` = 1;
