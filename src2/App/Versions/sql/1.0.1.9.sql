UPDATE `slconfig` SET `dbVersion` = '1.0.2.0' WHERE `slconfig`.`id` = 1;

ALTER TABLE `slconfig` 
  ADD `hudAllowDiscord` TINYINT(1) NOT NULL DEFAULT '0' AFTER `customLogoBin`, 
  ADD `hudDiscordLink` TEXT NOT NULL DEFAULT 'Not setup yet' AFTER `hudAllowDiscord`, 
  ADD `hudAllowGroup` TINYINT(1) NOT NULL DEFAULT '0' AFTER `hudDiscordLink`, 
  ADD `hudGroupLink` TEXT NOT NULL DEFAULT 'Not setup yet' AFTER `hudAllowGroup`, 
  ADD `hudAllowDetails` TINYINT(1) NOT NULL DEFAULT '0' AFTER `hudGroupLink`, 
  ADD `hudAllowRenewal` TINYINT(1) NOT NULL DEFAULT '0' AFTER `hudAllowDetails`;

ALTER TABLE `transactions` 
  ADD `SLtransactionUUID` VARCHAR(36) NULL AFTER `renew`, 
  ADD `ViaHud` TINYINT(1) NOT NULL DEFAULT '0' AFTER `SLtransactionUUID`, 
  ADD UNIQUE (`SLtransactionUUID`);