UPDATE `slconfig` SET `db_version` = '1.0.0.8' WHERE `slconfig`.`id` = 1;
ALTER TABLE `server` ADD `apilink` INT NOT NULL DEFAULT '1' AFTER `controlpanel_url`, ADD `api_username` TEXT NOT NULL AFTER `apilink`,
ADD `api_password` TEXT NOT NULL AFTER `api_username`, ADD `opt_password_reset` TINYINT(1) NOT NULL DEFAULT '1' AFTER `api_password`,
ADD `opt_autodj_next` TINYINT(1) NOT NULL DEFAULT '1' AFTER `opt_password_reset`, ADD `opt_toggle_autodj` TINYINT(1) NOT NULL DEFAULT '1' AFTER `opt_autodj_next`,
ADD `event_enable_start` TINYINT(1) NOT NULL DEFAULT '1' AFTER `opt_toggle_autodj`, ADD `event_disable_expire` TINYINT(1) NOT NULL DEFAULT '0' AFTER `event_enable_start`,
ADD `event_disable_revoke` TINYINT(1) NOT NULL DEFAULT '1' AFTER `event_disable_expire`, ADD `event_reset_password_revoke` TINYINT(1) NOT NULL DEFAULT '1' AFTER `event_disable_revoke`,
ADD INDEX (`apilink`);
CREATE TABLE `apis` (
  `id` int(11) NOT NULL,
  `name` text NOT NULL,
  `opt_password_reset` tinyint(1) NOT NULL DEFAULT 0,
  `opt_autodj_next` tinyint(1) NOT NULL DEFAULT 0,
  `opt_toggle_autodj` tinyint(1) NOT NULL DEFAULT 0,
  `event_enable_start` tinyint(1) NOT NULL DEFAULT 0,
  `event_enable_renew` tinyint(1) NOT NULL DEFAULT 0,
  `event_disable_expire` tinyint(1) NOT NULL DEFAULT 0,
  `event_disable_revoke` tinyint(1) NOT NULL DEFAULT 0,
  `event_reset_password_revoke` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
INSERT INTO `apis` (`id`, `name`, `opt_password_reset`, `opt_autodj_next`, `opt_toggle_autodj`, `event_enable_start`, `event_enable_renew`, `event_disable_expire`, `event_disable_revoke`, `event_reset_password_revoke`) VALUES
(1, 'No API', 0, 0, 0, 0, 0, 0, 0, 0),
(2, 'Centova v3', 0, 0, 0, 0, 0, 0, 0, 0),
(3, 'MediaCP', 0, 0, 0, 0, 0, 0, 0, 0),
(4, 'WHMSonic', 0, 0, 0, 0, 0, 0, 0, 0);
ALTER TABLE `apis`
  ADD PRIMARY KEY (`id`);
ALTER TABLE `apis`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
CREATE TABLE `api_requests` (
      `id` int(11) NOT NULL,
      `serverlink` int(11) NOT NULL,
      `rentallink` int(11) DEFAULT NULL,
      `streamlink` int(11) NOT NULL,
      `rental_start` tinyint(1) NOT NULL DEFAULT 0,
      `rental_renew` tinyint(1) NOT NULL DEFAULT 0,
      `rental_expire` tinyint(1) NOT NULL DEFAULT 0,
      `rental_remove` tinyint(1) NOT NULL DEFAULT 0,
      `autodj_toggle` tinyint(1) NOT NULL DEFAULT 0,
      `autodj_next` tinyint(1) NOT NULL DEFAULT 0,
      `password_reset` tinyint(1) NOT NULL DEFAULT 0
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
ALTER TABLE `api_requests`
      ADD PRIMARY KEY (`id`),
      ADD KEY `serverlink` (`serverlink`),
      ADD KEY `rentallink` (`rentallink`),
      ADD KEY `streamlink` (`streamlink`);
ALTER TABLE `api_requests`
        ADD CONSTRAINT `apirequest_rental_inuse` FOREIGN KEY (`rentallink`) REFERENCES `rental` (`id`) ON UPDATE NO ACTION,
        ADD CONSTRAINT `apirequest_server_inuse` FOREIGN KEY (`serverlink`) REFERENCES `server` (`id`) ON UPDATE NO ACTION,
        ADD CONSTRAINT `apirequest_stream_inuse` FOREIGN KEY (`streamlink`) REFERENCES `stream` (`id`) ON UPDATE NO ACTION;
ALTER TABLE `server` ADD CONSTRAINT `server_api_inuse` FOREIGN KEY (`apilink`) REFERENCES `apis`(`id`) ON DELETE RESTRICT ON UPDATE NO ACTION;
ALTER TABLE `server` CHANGE `api_username` `api_username` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL, CHANGE `api_password` `api_password` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL;
ALTER TABLE `api_requests`
  DROP `rental_start`,
  DROP `rental_renew`,
  DROP `rental_expire`,
  DROP `rental_remove`,
  DROP `autodj_toggle`,
  DROP `autodj_next`,
  DROP `password_reset`;
ALTER TABLE `api_requests` ADD `eventname` TEXT NOT NULL AFTER `streamlink`;
UPDATE `apis` SET `name` = 'none' WHERE `apis`.`id` = 1;
UPDATE `apis` SET `name` = 'centova3' WHERE `apis`.`id` = 2;
UPDATE `apis` SET `name` = 'mediacp' WHERE `apis`.`id` = 3;
UPDATE `apis` SET `name` = 'whmsonic' WHERE `apis`.`id` = 4;
ALTER TABLE `server` ADD `event_enable_renew` TINYINT(1) NOT NULL DEFAULT '1' AFTER `event_enable_start`; 
