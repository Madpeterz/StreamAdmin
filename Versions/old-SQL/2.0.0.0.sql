UPDATE `slconfig` SET `dbVersion` = '2.0.1.0' WHERE `slconfig`.`id` = 1;
ALTER TABLE `botconfig` DROP `httpToken`;