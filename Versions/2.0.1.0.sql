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
ALTER TABLE `server` 
ADD `cpuCores` INT NOT NULL DEFAULT '2' AFTER `ipaddress`, 
ADD `cpuSpeedMain` INT(3) NOT NULL AFTER `cpuCores`, 
ADD `cpuSpeedSub` INT(4) NOT NULL AFTER `cpuSpeedMain`, 
ADD `cpuSpeedType` VARCHAR(3) NOT NULL DEFAULT 'ghz' AFTER `cpuSpeedSub`, 
ADD `bandwidth` INT(5) NOT NULL DEFAULT '500' AFTER `cpuSpeedType`, 
ADD `bandwidthType` VARCHAR(4) NOT NULL DEFAULT 'mbps' AFTER `bandwidth`, 
ADD `totalStorage` INT NOT NULL DEFAULT '50' AFTER `bandwidthType`, 
ADD `totalStorageType` VARCHAR(3) NOT NULL DEFAULT 'gb' AFTER `totalStorage`; 
ALTER TABLE `server`
  DROP `cpuCores`,
  DROP `cpuSpeedMain`,
  DROP `cpuSpeedSub`,
  DROP `cpuSpeedType`; 
ALTER TABLE `slconfig` 
ADD `ansSalt` VARCHAR(30) NOT NULL DEFAULT 'Not used' AFTER `maxTotalStreams`, 
ADD `enableCoupons` TINYINT(1) NOT NULL DEFAULT '0' AFTER `ansSalt`; 
CREATE TABLE `marketplacecoupons` 
(
  `id` INT NOT NULL AUTO_INCREMENT , 
  `cost` INT NOT NULL DEFAULT '100' , 
  `listingid` INT NOT NULL , 
  `credit` INT NOT NULL DEFAULT '100' , PRIMARY KEY (`id`)
) ENGINE = InnoDB; 
ALTER TABLE `slconfig` 
CHANGE `ansSalt` `ansSalt` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Not used'; 
CREATE TABLE `coupons` (
  `id` INT NOT NULL AUTO_INCREMENT , 
  `unixtime` INT NOT NULL , 
  `couponLink` INT NOT NULL , 
  `TransactionID` BIGINT(20) NOT NULL , 
  `receiverAvatarLink` INT NOT NULL , 
  `payerAvatarLink` INT NOT NULL , 
  PRIMARY KEY (`id`), INDEX (`unixtime`), 
  INDEX (`couponLink`), INDEX (`receiverAvatarLink`), 
  INDEX (`payerAvatarLink`), UNIQUE (`TransactionID`)
  ) ENGINE = InnoDB; 
ALTER TABLE `coupons` ADD CONSTRAINT `coupons_marketplacecoupon_inuse` FOREIGN KEY (`couponLink`) REFERENCES `marketplacecoupons`(`id`) ON DELETE RESTRICT ON UPDATE NO ACTION; ALTER TABLE `coupons` ADD CONSTRAINT `coupons_payer_avatar_inuse` FOREIGN KEY (`payerAvatarLink`) REFERENCES `avatar`(`id`) ON DELETE RESTRICT ON UPDATE NO ACTION; ALTER TABLE `coupons` ADD CONSTRAINT `coupons_receiver_avatar_inuse` FOREIGN KEY (`receiverAvatarLink`) REFERENCES `avatar`(`id`) ON DELETE RESTRICT ON UPDATE NO ACTION; 
ALTER TABLE `marketplacecoupons` ADD UNIQUE(`listingid`); 
ALTER TABLE `avatar` ADD `credits` INT NOT NULL DEFAULT '0' AFTER `lastUsed`; 
ALTER TABLE `transactions` ADD `ViaMarketplace` TINYINT(1) NOT NULL DEFAULT '0' AFTER `ViaHud`; 
ALTER TABLE `transactions` ADD `targetAvatar` INT NULL DEFAULT NULL AFTER `ViaMarketplace`, ADD INDEX (`targetAvatar`); 
DROP TABLE `coupons`;
ALTER TABLE `transactions` ADD `fromCredits` TINYINT(1) NOT NULL DEFAULT '0' AFTER `targetAvatar`; 
ALTER TABLE `transactions` ADD CONSTRAINT `targetavatar_in_use_transactions` FOREIGN KEY (`targetAvatar`) REFERENCES `avatar`(`id`) ON DELETE RESTRICT ON UPDATE NO ACTION; 