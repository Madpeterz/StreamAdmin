UPDATE `slconfig` SET `dbVersion` = '1.0.2.4' WHERE `slconfig`.`id` = 1;

ALTER TABLE `rental` 
    ADD `apiSuspended` TINYINT(1) NOT NULL DEFAULT '0' AFTER `rentalUid`, 
    ADD `apiPendingSuspend` TINYINT(1) NOT NULL DEFAULT '0' AFTER `apiSuspended`, 
    ADD `apiPendingSuspendAfter` INT NULL DEFAULT NULL AFTER `apiPendingSuspend`;

ALTER TABLE `stream` 
    ADD `apiAllowSuspend` TINYINT(1) NOT NULL DEFAULT '1' AFTER `apiConfigValue3`;

ALTER TABLE `stream` CHANGE `apiAllowSuspend` `apiAllowAutoSuspend` TINYINT(1) NOT NULL DEFAULT '1';

ALTER TABLE `rental` CHANGE `apiPendingSuspend` `apiPendingAutoSuspend` TINYINT(1) NOT NULL DEFAULT '0';

ALTER TABLE `rental` CHANGE `apiPendingSuspendAfter` `apiPendingAutoSuspendAfter` INT(11) NULL DEFAULT NULL;

ALTER TABLE `package` 
    ADD `apiAllowAutoSuspend` TINYINT(1) NOT NULL DEFAULT '1' AFTER `enableGroupInvite`, 
    ADD `apiAutoSuspendDelayHours` INT NOT NULL DEFAULT '0' AFTER `apiAllowAutoSuspend`;

ALTER TABLE `stream` DROP `apiAllowAutoSuspend`;

ALTER TABLE `rental` 
  ADD `apiAllowAutoSuspend` TINYINT(1) NOT NULL DEFAULT '1' AFTER `rentalUid`;