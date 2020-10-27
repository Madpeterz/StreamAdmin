UPDATE `slconfig` SET `db_version` = '1.0.1.5' WHERE `slconfig`.`id` = 1;
ALTER TABLE `stream` ADD `api_id` TEXT NULL AFTER `last_api_sync`;
ALTER TABLE `apis` ADD `event_create_stream` TINYINT(1) NOT NULL DEFAULT '0' AFTER `event_recreate_revoke`, ADD `event_update_stream` TINYINT(1) NOT NULL DEFAULT '0' AFTER `event_create_stream`;
ALTER TABLE `server` ADD `event_create_stream` TINYINT(1) NOT NULL DEFAULT '0' AFTER `last_api_sync`, ADD `event_update_stream` TINYINT(1) NOT NULL DEFAULT '0' AFTER `event_create_stream`;
ALTER TABLE `stream` CHANGE `api_id` `api_uid` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL;
ALTER TABLE `stream` CHANGE `api_uid` `api_uid_1` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL;
ALTER TABLE `stream` ADD `api_uid_2` TEXT NULL AFTER `api_uid_1`;
