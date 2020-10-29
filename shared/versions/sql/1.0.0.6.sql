CREATE TABLE `banlist` ( `id` int(11) NOT NULL, `avatar_link` int(11) NOT NULL ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
ALTER TABLE `banlist` ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `avatar_link` (`avatar_link`);
ALTER TABLE `banlist` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `banlist` ADD CONSTRAINT `avatar_in_use_banlist` FOREIGN KEY (`avatar_link`) REFERENCES `avatar`(`id`) ON DELETE RESTRICT ON UPDATE NO ACTION;
UPDATE `slconfig` SET `db_version` = '1.0.0.7' WHERE `slconfig`.`id` = 1;
