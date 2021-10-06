UPDATE `slconfig` SET `db_version` = '1.0.1.3' WHERE `slconfig`.`id` = 1;
ALTER TABLE `stream` ADD `last_api_sync` INT NOT NULL DEFAULT '0' AFTER `mountpoint`;
ALTER TABLE `server` ADD `last_api_sync` INT NOT NULL DEFAULT '0' AFTER `event_recreate_revoke`; 
