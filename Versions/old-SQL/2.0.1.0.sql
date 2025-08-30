ALTER TABLE `rental` CHANGE `renewals` `renewals` INT(11) NOT NULL DEFAULT '0';
UPDATE `slconfig` SET `dbVersion` = '2.0.1.1' WHERE `slconfig`.`id` = 1;