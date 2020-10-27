UPDATE `slconfig` SET `db_version` = '1.0.1.5' WHERE `slconfig`.`id` = 1;
ALTER TABLE `stream` ADD `api_id` TEXT NULL AFTER `last_api_sync`;
ALTER TABLE `apis` ADD `event_create_stream` TINYINT(1) NOT NULL DEFAULT '0' AFTER `event_recreate_revoke`, ADD `event_update_stream` TINYINT(1) NOT NULL DEFAULT '0' AFTER `event_create_stream`;
ALTER TABLE `server` ADD `event_create_stream` TINYINT(1) NOT NULL DEFAULT '0' AFTER `last_api_sync`, ADD `event_update_stream` TINYINT(1) NOT NULL DEFAULT '0' AFTER `event_create_stream`; 
