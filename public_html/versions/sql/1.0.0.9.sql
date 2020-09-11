UPDATE `slconfig` SET `db_version` = '1.0.1.0' WHERE `slconfig`.`id` = 1;
UPDATE `apis` SET `opt_toggle_status` = '1', `opt_password_reset` = '1', `opt_autodj_next` = '1', `opt_toggle_autodj` = '1', `event_enable_start` = '1', `event_enable_renew` = '1', `event_disable_expire` = '1', `event_disable_revoke` = '1', `event_reset_password_revoke` = '1' WHERE `apis`.`id` = 2;
ALTER TABLE `server` ADD `event_start_sync_username` TINYINT(1) NOT NULL DEFAULT '0' AFTER `event_enable_start`;
ALTER TABLE `apis` ADD `event_start_sync_username` TINYINT(1) NOT NULL DEFAULT '0' AFTER `event_enable_start`;
UPDATE `apis` SET `event_start_sync_username` = '1' WHERE `apis`.`id` = 2;
ALTER TABLE `stream` ADD `original_adminusername` TEXT NOT NULL AFTER `needwork`;
UPDATE `stream` SET `original_adminusername` = `stream`.`adminusername`;
ALTER TABLE `apis` ADD `api_serverstatus` TINYINT(1) NOT NULL DEFAULT '0' AFTER `name`;
ALTER TABLE `server` ADD `api_serverstatus` TINYINT(1) NOT NULL DEFAULT '1' AFTER `api_password`;
UPDATE `apis` SET `api_serverstatus` = '1' WHERE `apis`.`id` = 2; 