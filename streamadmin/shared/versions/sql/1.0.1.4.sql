UPDATE `slconfig` SET `db_version` = '1.0.1.5' WHERE `slconfig`.`id` = 1;
ALTER TABLE `stream` ADD `api_id` TEXT NULL AFTER `last_api_sync`;
ALTER TABLE `apis` ADD `event_create_stream` TINYINT(1) NOT NULL DEFAULT '0' AFTER `event_recreate_revoke`, ADD `event_update_stream` TINYINT(1) NOT NULL DEFAULT '0' AFTER `event_create_stream`;
ALTER TABLE `server` ADD `event_create_stream` TINYINT(1) NOT NULL DEFAULT '0' AFTER `last_api_sync`, ADD `event_update_stream` TINYINT(1) NOT NULL DEFAULT '0' AFTER `event_create_stream`;
ALTER TABLE `stream` CHANGE `api_id` `api_uid` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL;
ALTER TABLE `stream` CHANGE `api_uid` `api_uid_1` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL;
ALTER TABLE `stream` ADD `api_uid_2` TEXT NULL AFTER `api_uid_1`;
INSERT INTO `apis` (`id`, `name`, `api_serverstatus`, `api_sync_accounts`, `opt_toggle_status`, `opt_password_reset`, `opt_autodj_next`, `opt_toggle_autodj`, `event_enable_start`, `event_start_sync_username`, `event_enable_renew`, `event_disable_expire`, `event_disable_revoke`, `event_revoke_reset_username`, `event_reset_password_revoke`, `event_clear_djs`, `event_recreate_revoke`, `event_create_stream`, `event_update_stream`) VALUES (NULL, 'AzureCast', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1');
UPDATE `apis` SET `name` = 'azuracast' WHERE `apis`.`id` = 6; 
