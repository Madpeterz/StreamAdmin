UPDATE `slconfig` SET `dbVersion` = '1.0.1.9' WHERE `slconfig`.`id` = 1;

ALTER TABLE `package` 
ADD `welcomeNotecardLink` INT NOT NULL DEFAULT '1' AFTER `apiTemplate`, 
ADD `setupNotecardLink` INT NOT NULL DEFAULT '1' AFTER `welcomeNotecardLink`, 
ADD INDEX (`welcomeNotecardLink`), ADD INDEX (`setupNotecardLink`);

ALTER TABLE `package` ADD CONSTRAINT `noticenotecard_in_use_package_1` 
FOREIGN KEY (`welcomeNotecardLink`) REFERENCES `noticenotecard`(`id`) 
ON DELETE RESTRICT ON UPDATE NO ACTION; 
ALTER TABLE `package` ADD CONSTRAINT `noticenotecard_in_use_package_2` 
FOREIGN KEY (`setupNotecardLink`) REFERENCES `noticenotecard`(`id`) 
ON DELETE RESTRICT ON UPDATE NO ACTION;

CREATE TABLE `notecardmail` ( 
    `id` INT NOT NULL AUTO_INCREMENT , 
    `avatarLink` INT NOT NULL , 
    `noticenotecardLink` INT NOT NULL , 
    PRIMARY KEY (`id`)
) ENGINE = InnoDB;