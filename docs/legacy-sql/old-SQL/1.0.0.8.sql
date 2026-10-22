UPDATE `slconfig` SET `db_version` = '1.0.0.9' WHERE `slconfig`.`id` = 1;
ALTER TABLE `server` ADD `api_url` TEXT NULL AFTER `apilink`;
ALTER TABLE `apis` ADD `opt_toggle_status` TINYINT(1) NOT NULL DEFAULT '0' AFTER `name`;
ALTER TABLE `server` ADD `opt_toggle_status` TINYINT(1) NOT NULL DEFAULT '1' AFTER `opt_toggle_autodj`;
