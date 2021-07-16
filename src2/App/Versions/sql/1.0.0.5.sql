ALTER TABLE `notecard` ADD `as_notice` TINYINT(1) NOT NULL DEFAULT '0' AFTER `rentallink`,
    ADD `noticelink` INT NULL DEFAULT NULL AFTER `as_notice`, ADD INDEX (`noticelink`);

ALTER TABLE `notecard` ADD CONSTRAINT `notecard_ibfk_2` FOREIGN KEY (`noticelink`)
    REFERENCES `notice`(`id`) ON DELETE RESTRICT ON UPDATE NO ACTION;

ALTER TABLE `notice` ADD `notecarddetail` TEXT NOT NULL AFTER `usebot`;

ALTER TABLE `notice` ADD `send_notecard` TINYINT(1) NOT NULL DEFAULT '0' AFTER `usebot`;

UPDATE `slconfig` SET `db_version` = '1.0.0.6' WHERE `slconfig`.`id` = 1;
