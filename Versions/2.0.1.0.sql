UPDATE `slconfig` SET `dbVersion` = '2.0.1.1' WHERE `slconfig`.`id` = 1;
ALTER TABLE `slconfig` 
ADD `limitStreams` TINYINT(1) NOT NULL DEFAULT '0' AFTER `clientsDisplayServer`, 
ADD `limitTime` TINYINT(1) NOT NULL DEFAULT '0' AFTER `limitStreams`, 
ADD `maxStreamTimeDays` INT NOT NULL DEFAULT '120' AFTER `limitTime`, 
ADD `maxTotalStreams` INT NOT NULL DEFAULT '100' AFTER `maxStreamTimeDays`; 
ALTER TABLE `package` 
ADD `enforceCustomMaxStreams` TINYINT(1) NOT NULL DEFAULT '0' AFTER `enableGroupInvite`, 
ADD `maxStreamsInPackage` INT NOT NULL DEFAULT '1' AFTER `enforceCustomMaxStreams`; 
ALTER TABLE `server` 
ADD `ipaddress` TEXT NULL DEFAULT NULL AFTER `controlPanelURL`, ADD UNIQUE (`ipaddress`); 
ALTER TABLE `avatar` ADD `lastUsed` INT NOT NULL DEFAULT '1729026128' AFTER `avatarUid`; 