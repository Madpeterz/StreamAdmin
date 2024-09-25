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

CREATE TABLE `auditlog` (
  `id` int(11) NOT NULL,
  `store` varchar(12) NOT NULL,
  `sourceid` varchar(8) DEFAULT NULL,
  `valuename` text NOT NULL,
  `oldvalue` text DEFAULT NULL,
  `newvalue` text DEFAULT NULL,
  `unixtime` int(11) NOT NULL,
  `avatarLink` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `auditlog`
  ADD PRIMARY KEY (`id`),
  ADD KEY `staffLink` (`avatarLink`);

ALTER TABLE `auditlog`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `auditlog`
  ADD CONSTRAINT `auditlog_staff_inuse` FOREIGN KEY (`avatarLink`) REFERENCES `avatar` (`id`) ON UPDATE NO ACTION;

  ALTER TABLE `objects` ADD UNIQUE(`objectUUID`);