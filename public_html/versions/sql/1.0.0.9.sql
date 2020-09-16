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
