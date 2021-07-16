ALTER TABLE `package` CHANGE `name` `name` VARCHAR(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL;
ALTER TABLE `transactions` ADD `renew` TINYINT(1) NOT NULL DEFAULT '0' AFTER `transaction_uid`;
UPDATE `slconfig` SET `db_version` = '1.0.0.4' WHERE `slconfig`.`id` = 1;
