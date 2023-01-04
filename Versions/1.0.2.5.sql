DROP TABLE `apirequests`;

ALTER TABLE `server` DROP FOREIGN KEY `api_in_use_server`;

ALTER TABLE `server` 
DROP `apiLink`, DROP `apiURL`, DROP `apiUsername`, DROP `apiPassword`, DROP `apiServerStatus`, 
DROP `apiSyncAccounts`, DROP `optPasswordReset`, DROP `optAutodjNext`, DROP `optToggleAutodj`, 
DROP `optToggleStatus`, DROP `eventEnableStart`, DROP `eventStartSyncUsername`, DROP `eventEnableRenew`, 
DROP `eventDisableExpire`, DROP `eventDisableRevoke`, DROP `eventRevokeResetUsername`, DROP `eventResetPasswordRevoke`, 
DROP `eventClearDjs`, DROP `eventRecreateRevoke`, DROP `lastApiSync`, DROP `eventCreateStream`, DROP `eventUpdateStream`;

DROP TABLE `apis`;

UPDATE `slconfig` SET `dbVersion` = '2.0.0.0' WHERE `slconfig`.`id` = 1;

ALTER TABLE `rental`
  DROP `apiAllowAutoSuspend`,
  DROP `apiSuspended`,
  DROP `apiPendingAutoSuspend`,
  DROP `apiPendingAutoSuspendAfter`;

ALTER TABLE `stream`
  DROP `lastApiSync`,
  DROP `apiConfigValue1`,
  DROP `apiConfigValue2`,
  DROP `apiConfigValue3`;

ALTER TABLE `package`
  DROP `apiAllowAutoSuspend`,
  DROP `apiAutoSuspendDelayHours`;

ALTER TABLE `slconfig` DROP `apiDefaultEmail`;

ALTER TABLE `notecardmail` 
ADD CONSTRAINT `notecardmail_avatar_inuse` FOREIGN KEY (`avatarLink`) REFERENCES `avatar`(`id`) ON DELETE RESTRICT ON UPDATE NO ACTION; 
ALTER TABLE `notecardmail` 
ADD CONSTRAINT `notecardmial_notecard_inuse` FOREIGN KEY (`noticenotecardLink`) REFERENCES `noticenotecard`(`id`) ON DELETE RESTRICT ON UPDATE NO ACTION;

UPDATE `stream` set `adminUsername` = `originalAdminUsername` WHERE 1=1;

ALTER TABLE `stream` DROP `originalAdminUsername`;

ALTER TABLE `package` DROP `apiTemplate`;

ALTER TABLE `slconfig`
  DROP `customLogo`,
  DROP `customLogoBin`;

ALTER TABLE `slconfig` 
ADD `streamListOption` INT NOT NULL DEFAULT '0' AFTER `paymentKey`, 
ADD `clientsDisplayServer` TINYINT(1) NOT NULL DEFAULT '0' AFTER `streamListOption`;

CREATE TABLE `auditlog` (`id` INT NOT NULL AUTO_INCREMENT , `store` VARCHAR(12) NOT NULL , `sourceid` INT NOT NULL , `valuename` TEXT NOT NULL , `oldvalue` TEXT NOT NULL , `newvalue` TEXT NOT NULL , `unixtime` INT NOT NULL , `staffLink` INT NOT NULL , PRIMARY KEY (`id`), INDEX (`staffLink`)) ENGINE = InnoDB;

ALTER TABLE `auditlog` ADD CONSTRAINT `auditlog_staff_inuse` FOREIGN KEY (`staffLink`) REFERENCES `staff`(`id`) ON DELETE RESTRICT ON UPDATE NO ACTION;

ALTER TABLE `auditlog` CHANGE `staffLink` `avatarLink` INT(11) NOT NULL;

ALTER TABLE `auditlog` DROP FOREIGN KEY `auditlog_staff_inuse`; ALTER TABLE `auditlog` ADD CONSTRAINT `auditlog_staff_inuse` FOREIGN KEY (`avatarLink`) REFERENCES `avatar`(`id`) ON DELETE RESTRICT ON UPDATE NO ACTION;

ALTER TABLE `auditlog` CHANGE `sourceid` `sourceid` INT(11) NULL;

ALTER TABLE `auditlog` CHANGE `oldvalue` `oldvalue` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL, CHANGE `newvalue` `newvalue` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL;

ALTER TABLE `auditlog` CHANGE `sourceid` `sourceid` VARCHAR(8) NULL DEFAULT NULL;