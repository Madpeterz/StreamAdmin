ALTER TABLE `transactions` CHANGE `packagelink` `packagelink` INT(11) NULL DEFAULT NULL,
    CHANGE `resellerlink` `resellerlink` INT(11) NULL DEFAULT NULL,
    CHANGE `regionlink` `regionlink` INT(11) NULL DEFAULT NULL;

UPDATE `slconfig` SET `db_version` = '1.0.0.3' WHERE `slconfig`.`id` = 1;
