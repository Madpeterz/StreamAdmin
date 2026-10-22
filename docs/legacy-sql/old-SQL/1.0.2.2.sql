UPDATE `slconfig` SET `dbVersion` = '1.0.2.3' WHERE `slconfig`.`id` = 1;
ALTER TABLE `slconfig` 
    CHANGE `hudDiscordLink` `hudDiscordLink` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT "Not setup yet", 
    CHANGE `hudGroupLink` `hudGroupLink` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT "Not setup yet";

ALTER TABLE `textureconfig` DROP `treevendWaiting`;

ALTER TABLE `treevender` 
    ADD `textureWaiting` VARCHAR(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000' AFTER `name`, 
    ADD `textureInuse` VARCHAR(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000' AFTER `textureWaiting`;

CREATE TABLE `datatable` (
  `id` int(11) NOT NULL,
  `col` int(11) NOT NULL DEFAULT 0,
  `cols` text NOT NULL,
  `name` text NOT NULL,
  `dir` text NOT NULL DEFAULT 'desc'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `datatable` ADD PRIMARY KEY(`id`);

ALTER TABLE `datatable` CHANGE `id` `id` INT(11) NOT NULL AUTO_INCREMENT;
INSERT INTO `datatable` (`id`, `col`, `cols`, `name`, `dir`) VALUES (NULL, '0', '0=id,1=region,3=Percentage,4=Count Up,5=Count down', 'health', 'desc');
ALTER TABLE `datatable` ADD `hideColZero` TINYINT(1) NOT NULL DEFAULT '1' AFTER `id`;
INSERT INTO `datatable` (`id`, `hideColZero`, `col`, `cols`, `name`, `dir`) VALUES (NULL, '1', '1', '0=id,1=Object,2=Last seen,5=Owner', 'Health / Detailed', 'desc');
UPDATE `datatable` SET `name` = 'Health' WHERE `datatable`.`id` = 1;
INSERT INTO `datatable` (`id`, `hideColZero`, `col`, `cols`, `name`, `dir`) VALUES (NULL, '1', '0', '0=id,1=Rental UID,2=Avatar,3=Port,6=Renewals', 'Client / List', 'desc');
INSERT INTO `datatable` (`id`, `hideColZero`, `col`, `cols`, `name`, `dir`) VALUES (NULL, '1', '0', '0=id,1=Stream UID,2=Server,3=Port', 'Stream / List', 'desc');
INSERT INTO `datatable` (`id`, `hideColZero`, `col`, `cols`, `name`, `dir`) VALUES (NULL, '1', '0', '0=id,1=Package name,2=Sold,3=Need work,4=Ready', 'Streams / Package menu', 'desc');
INSERT INTO `datatable` (`id`, `hideColZero`, `col`, `cols`, `name`, `dir`) VALUES (NULL, '1', '0', '0=id,1=Package UID,2=Name,4=Listeners,5=Days,6=Kbps,7=Cost', 'Packages / List', 'desc');