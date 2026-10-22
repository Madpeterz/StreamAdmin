UPDATE `slconfig` SET `dbVersion` = '1.0.2.2' WHERE `slconfig`.`id` = 1;
ALTER TABLE `slconfig` 
    ADD `eventsAPI` TINYINT(1) NOT NULL DEFAULT '0' AFTER `hudAllowRenewal`;
CREATE TABLE `eventsq` ( 
    `id` INT NOT NULL AUTO_INCREMENT ,
    `eventName` TEXT NOT NULL , 
    `eventMessage` TEXT NOT NULL , 
    `eventUnixtime` INT NOT NULL , 
    PRIMARY KEY (`id`)
) ENGINE = InnoDB;
ALTER TABLE `botconfig` 
    ADD `invites` TINYINT(1) NOT NULL DEFAULT '0' AFTER `ims`, 
    ADD `inviteGroupUUID` VARCHAR(36) NULL DEFAULT NULL AFTER `invites`;
ALTER TABLE `package` 
    ADD `enableGroupInvite` TINYINT(1) NOT NULL DEFAULT '1' AFTER `setupNotecardLink`;