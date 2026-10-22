UPDATE `slconfig` SET `db_version` = '1.0.1.4' WHERE `slconfig`.`id` = 1;
ALTER TABLE `objects` CHANGE `objectmode` `objectmode` VARCHAR(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL;
ALTER TABLE `objects` ADD INDEX(`objectmode`);
