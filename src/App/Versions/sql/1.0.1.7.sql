UPDATE `slconfig` SET `dbVersion` = '1.0.1.8' WHERE `slconfig`.`id` = 1;
ALTER TABLE `slconfig` 
ADD `customLogo` TINYINT(1) NOT NULL DEFAULT '0' AFTER `apiDefaultEmail`, 
ADD `customLogoBin` TEXT NOT NULL AFTER `customLogo`;