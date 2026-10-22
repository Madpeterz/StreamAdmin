UPDATE `slconfig` SET `dbVersion` = '1.0.2.5' WHERE `slconfig`.`id` = 1;
ALTER TABLE `notice` ADD `sendObjectIM` TINYINT(1) NOT NULL DEFAULT '1' AFTER `imMessage`;

CREATE TABLE `rentalnoticeptout` ( 
        `id` INT NOT NULL AUTO_INCREMENT , 
        `rentalLink` INT NOT NULL , 
        `noticeLink` INT NOT NULL , 
        `enabled` TINYINT(1) NOT NULL DEFAULT '1' , 
        PRIMARY KEY (`id`), 
        INDEX (`rentalLink`), 
        INDEX (`noticeLink`)
) ENGINE = InnoDB;

ALTER TABLE `rentalnoticeptout` 
  ADD CONSTRAINT `table: rental notice optout - Rental in use` FOREIGN KEY (`rentalLink`) REFERENCES `rental`(`id`) ON DELETE RESTRICT ON UPDATE NO ACTION; 
  
ALTER TABLE `rentalnoticeptout` 
  ADD CONSTRAINT `table: rental notice optout - Notice in use` FOREIGN KEY (`noticeLink`) REFERENCES `notice`(`id`) ON DELETE RESTRICT ON UPDATE NO ACTION;

ALTER TABLE `rentalnoticeptout` DROP `enabled`;