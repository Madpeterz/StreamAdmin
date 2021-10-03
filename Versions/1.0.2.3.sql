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

CREATE TABLE `botcommandq` ( 
     `id` INT NOT NULL AUTO_INCREMENT , 
     `command` TEXT NOT NULL , 
     `arg1` TEXT NULL , 
     `arg2` TEXT NULL , 
     `arg3` TEXT NULL , 
     `arg4` TEXT NULL , 
     `arg5` TEXT NULL , 
     `unixtime` INT NOT NULL , 
PRIMARY KEY (`id`), INDEX (`unixtime`)) ENGINE = InnoDB;

ALTER TABLE `botcommandq` CHANGE `arg1` `args` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL;

ALTER TABLE `botcommandq`
  DROP `arg2`,
  DROP `arg3`,
  DROP `arg4`,
  DROP `arg5`;

ALTER TABLE `botconfig` 
  ADD `httpMode` TINYINT(1) NOT NULL DEFAULT '0' AFTER `inviteGroupUUID`, 
  ADD `httpURL` TEXT NULL AFTER `httpMode`, 
  ADD `httpToken` TEXT NULL AFTER `httpURL`;

UPDATE `datatable` SET `cols` = '0=id,1=Rental UID,2=Avatar,3=Port,5=Timeleft,6=Status,7=Renewals' WHERE `id` = 3;

ALTER TABLE `slconfig` ADD `paymentKey` TEXT NULL AFTER `eventsAPI`;