UPDATE `slconfig` SET `db_version` = '1.0.1.2' WHERE `slconfig`.`id` = 1;
ALTER TABLE `apis` ADD `api_sync_accounts` TINYINT(1) NOT NULL DEFAULT '0' AFTER `api_serverstatus`;
UPDATE `apis` SET `api_sync_accounts` = '1' WHERE `apis`.`id` = 2;
ALTER TABLE `server` ADD `api_sync_accounts` TINYINT(1) NOT NULL DEFAULT '0' AFTER `api_serverstatus`;
