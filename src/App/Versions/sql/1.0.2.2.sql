UPDATE `slconfig` SET `dbVersion` = '1.0.2.3' WHERE `slconfig`.`id` = 1;
ALTER TABLE `slconfig` 
    CHANGE `hudDiscordLink` `hudDiscordLink` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT "Not setup yet", 
    CHANGE `hudGroupLink` `hudGroupLink` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT "Not setup yet";

ALTER TABLE `textureconfig` DROP `treevendWaiting`;

ALTER TABLE `treevender` 
    ADD `textureWaiting` VARCHAR(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000' AFTER `name`, 
    ADD `textureInuse` VARCHAR(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000' AFTER `textureWaiting`;