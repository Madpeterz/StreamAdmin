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
ALTER TABLE `apis` ADD `event_clear_djs` TINYINT(1) NOT NULL DEFAULT '0' AFTER `event_reset_password_revoke`;
ALTER TABLE `server` ADD `event_clear_djs` TINYINT(1) NOT NULL DEFAULT '0' AFTER `event_reset_password_revoke`;
UPDATE `apis` SET `event_clear_djs` = '1' WHERE `apis`.`id` = 2;
ALTER TABLE `api_requests` ADD `attempts` INT NOT NULL DEFAULT '0' AFTER `eventname`;
ALTER TABLE `api_requests` ADD `last_attempt` INT NOT NULL DEFAULT '0' AFTER `attempts`;
ALTER TABLE `api_requests` ADD `last_failed_why` TEXT NULL AFTER `last_attempt`;
ALTER TABLE `api_requests` CHANGE `id` `id` INT(11) NOT NULL AUTO_INCREMENT;
CREATE TABLE `notice_notecard` (
  `id` int(11) NOT NULL,
  `name` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `notice_notecard` (`id`, `name`) VALUES
(1, 'none');


ALTER TABLE `notice_notecard`
  ADD PRIMARY KEY (`id`);


ALTER TABLE `notice_notecard`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `notice` ADD `notice_notecardlink` INT NOT NULL DEFAULT '1' AFTER `hoursremaining`, ADD INDEX (`notice_notecardlink`);

ALTER TABLE `notice` ADD CONSTRAINT `notice_notice_notecard_inuse` FOREIGN KEY (`notice_notecardlink`) REFERENCES `notice_notecard`(`id`) ON DELETE RESTRICT ON UPDATE NO ACTION;
ALTER TABLE `notice_notecard` ADD `missing` TINYINT(1) NOT NULL DEFAULT '0' AFTER `name`;
ALTER TABLE `notice_notecard` CHANGE `name` `name` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL;
ALTER TABLE `notice_notecard` ADD UNIQUE(`name`);
ALTER TABLE `api_requests` CHANGE `last_failed_why` `message` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL;

ALTER TABLE `apis` ADD `event_revoke_reset_username` TINYINT(1) NOT NULL DEFAULT '1' AFTER `event_disable_revoke`;
ALTER TABLE `server` ADD `event_revoke_reset_username` TINYINT(1) NOT NULL DEFAULT '1' AFTER `event_disable_revoke`;

ALTER TABLE `apis` ADD `event_recreate_revoke` TINYINT(1) NOT NULL DEFAULT '0' AFTER `event_clear_djs`;
ALTER TABLE `server` ADD `event_recreate_revoke` TINYINT(1) NOT NULL DEFAULT '0' AFTER `event_clear_djs`;
UPDATE `apis` SET `event_disable_revoke` = '0', `event_revoke_reset_username` = '0', `event_reset_password_revoke` = '0', `event_recreate_revoke` = '1' WHERE `apis`.`id` = 2;
ALTER TABLE `package` ADD `api_template` TEXT NULL AFTER `texture_uuid_instock_selected`;
UPDATE `apis` SET `event_disable_revoke` = '1', `event_revoke_reset_username` = '1', `event_reset_password_revoke` = '1' WHERE `apis`.`id` = 2;


CREATE TABLE `servertypes` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `servertypes` (`id`, `name`) VALUES
(1, 'ShoutcastV1'),
(2, 'ShoutcastV2'),
(3, 'Icecast');


ALTER TABLE `servertypes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);


ALTER TABLE `servertypes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

ALTER TABLE `package` ADD `servertypelink` INT NOT NULL DEFAULT '1' AFTER `templatelink`, ADD INDEX (`servertypelink`);

ALTER TABLE `package` ADD CONSTRAINT `package_ibfk_2` FOREIGN KEY (`servertypelink`) REFERENCES `servertypes`(`id`) ON DELETE RESTRICT ON UPDATE NO ACTION;

ALTER TABLE `apis` CHANGE `event_revoke_reset_username` `event_revoke_reset_username` TINYINT(1) NOT NULL DEFAULT '0';

UPDATE `apis` SET `event_disable_revoke` = '1', `event_revoke_reset_username` = '1', `event_reset_password_revoke` = '1' WHERE `apis`.`id` = 2;

UPDATE `apis` SET `event_revoke_reset_username` = '0' WHERE `apis`.`id` = 1; UPDATE `apis` SET `event_revoke_reset_username` = '0' WHERE `apis`.`id` = 3; UPDATE `apis` SET `event_revoke_reset_username` = '0' WHERE `apis`.`id` = 4;

ALTER TABLE `server` CHANGE `api_serverstatus` `api_serverstatus` TINYINT(1) NOT NULL DEFAULT '0', CHANGE `opt_password_reset` `opt_password_reset` TINYINT(1) NOT NULL DEFAULT '0', CHANGE `opt_autodj_next` `opt_autodj_next` TINYINT(1) NOT NULL DEFAULT '0', CHANGE `opt_toggle_autodj` `opt_toggle_autodj` TINYINT(1) NOT NULL DEFAULT '0', CHANGE `opt_toggle_status` `opt_toggle_status` TINYINT(1) NOT NULL DEFAULT '0', CHANGE `event_enable_start` `event_enable_start` TINYINT(1) NOT NULL DEFAULT '0', CHANGE `event_enable_renew` `event_enable_renew` TINYINT(1) NOT NULL DEFAULT '0', CHANGE `event_disable_revoke` `event_disable_revoke` TINYINT(1) NOT NULL DEFAULT '0', CHANGE `event_revoke_reset_username` `event_revoke_reset_username` TINYINT(1) NOT NULL DEFAULT '0', CHANGE `event_reset_password_revoke` `event_reset_password_revoke` TINYINT(1) NOT NULL DEFAULT '0';

INSERT INTO `apis` (`id`, `name`, `api_serverstatus`, `opt_toggle_status`, `opt_password_reset`, `opt_autodj_next`, `opt_toggle_autodj`, `event_enable_start`, `event_start_sync_username`, `event_enable_renew`, `event_disable_expire`, `event_disable_revoke`, `event_revoke_reset_username`, `event_reset_password_revoke`, `event_clear_djs`, `event_recreate_revoke`)
VALUES (NULL, 'secondbot', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0');
