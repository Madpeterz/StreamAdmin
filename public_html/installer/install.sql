SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";

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
(1, 'none', 0, 0, 0, 0, 0, 0, 0, 0),
(2, 'centova3', 0, 0, 0, 0, 0, 0, 0, 0),
(3, 'mediacp', 0, 0, 0, 0, 0, 0, 0, 0),
(4, 'whmsonic', 0, 0, 0, 0, 0, 0, 0, 0);

CREATE TABLE `api_requests` (
  `id` int(11) NOT NULL,
  `serverlink` int(11) NOT NULL,
  `rentallink` int(11) DEFAULT NULL,
  `streamlink` int(11) NOT NULL,
  `eventname` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `avatar` (
  `id` int(11) NOT NULL,
  `avataruuid` varchar(36) NOT NULL,
  `avatarname` text NOT NULL,
  `avatar_uid` varchar(8) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `avatar` (`id`, `avataruuid`, `avatarname`, `avatar_uid`) VALUES
(1, 'system', 'Madpeter Zond', 'system');

CREATE TABLE `banlist` (
  `id` int(11) NOT NULL,
  `avatar_link` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `botconfig` (
  `id` int(11) NOT NULL,
  `avatarlink` int(11) NOT NULL,
  `secret` text DEFAULT NULL,
  `notecards` tinyint(1) NOT NULL DEFAULT 0,
  `ims` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `botconfig` (`id`, `avatarlink`, `secret`, `notecards`, `ims`) VALUES
(1, 1, 'notsetup', 1, 0);

CREATE TABLE `detail` (
  `id` int(11) NOT NULL,
  `rentallink` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `event` (
  `id` int(11) NOT NULL,
  `avatar_uuid` varchar(36) NOT NULL,
  `avatar_name` text NOT NULL,
  `rental_uid` varchar(8) NOT NULL,
  `package_uid` varchar(8) NOT NULL,
  `event_new` tinyint(1) NOT NULL DEFAULT 0,
  `event_renew` tinyint(1) NOT NULL DEFAULT 0,
  `event_expire` tinyint(1) NOT NULL DEFAULT 0,
  `event_remove` tinyint(1) NOT NULL DEFAULT 0,
  `unixtime` int(11) NOT NULL,
  `expire_unixtime` int(11) NOT NULL,
  `port` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `message` (
  `id` int(11) NOT NULL,
  `avatarlink` int(11) NOT NULL,
  `message` varchar(900) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `message` (`id`, `avatarlink`, `message`) VALUES
(1, 1, 'Web panel setup finished please reset your password');

CREATE TABLE `notecard` (
  `id` int(11) NOT NULL,
  `rentallink` int(11) NOT NULL,
  `as_notice` tinyint(1) NOT NULL DEFAULT 0,
  `noticelink` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `notice` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `immessage` varchar(800) NOT NULL,
  `usebot` tinyint(1) NOT NULL DEFAULT 0,
  `send_notecard` tinyint(1) NOT NULL DEFAULT 0,
  `notecarddetail` text NOT NULL,
  `hoursremaining` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `notice` (`id`, `name`, `immessage`, `usebot`, `send_notecard`, `notecarddetail`, `hoursremaining`) VALUES
(1, '7 day notice', 'Hello [[AVATAR_FIRSTNAME]] your stream on [[SERVER_DOMAIN]] port [[STREAM_PORT]] now has [[RENTAL_TIMELEFT]] remaining', 1, 0, '', 168),
(2, '5 day notice', 'Hello [[AVATAR_FIRSTNAME]] your stream on [[SERVER_DOMAIN]] port [[STREAM_PORT]] now has [[RENTAL_TIMELEFT]]  remaining, When you have time please drop into our store.', 1, 0, '', 120),
(3, '3 day notice', 'Hello [[AVATAR_FIRSTNAME]] your stream on [[SERVER_DOMAIN]] port [[STREAM_PORT]] now has [[RENTAL_TIMELEFT]] remaining, Dont forget to renew your service!', 1, 0, '', 72),
(4, '1 day notice', 'Hello [[AVATAR_FIRSTNAME]] your stream on [[SERVER_DOMAIN]] port [[STREAM_PORT]] now has less than 24 hours remaining. Please renew to avoid loss of service.', 1, 0, '', 24),
(5, '5 hour notice', 'Hello [[AVATAR_FIRSTNAME]] your stream on [[SERVER_DOMAIN]] port [[STREAM_PORT]] now has less than 5 hours remaining. ', 1, 0, '', 5),
(6, 'Expired', 'Hello [[AVATAR_FIRSTNAME]] your stream on [[SERVER_DOMAIN]] port [[STREAM_PORT]]  has now expired please renew asap or risk losing the assigned port.', 1, 0, '', 0),
(10, 'Active', '', 0, 0, '', 999);

CREATE TABLE `objects` (
  `id` int(11) NOT NULL,
  `avatarlink` int(11) NOT NULL,
  `regionlink` int(11) NOT NULL,
  `objectuuid` varchar(36) NOT NULL,
  `objectname` text NOT NULL,
  `objectmode` text NOT NULL,
  `objectxyz` text NOT NULL,
  `lastseen` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `package` (
  `id` int(11) NOT NULL,
  `package_uid` varchar(8) NOT NULL,
  `name` varchar(60) NOT NULL,
  `autodj` tinyint(1) NOT NULL DEFAULT 0,
  `autodj_size` text DEFAULT NULL,
  `listeners` int(11) DEFAULT NULL,
  `bitrate` int(11) DEFAULT NULL,
  `templatelink` int(11) DEFAULT NULL,
  `cost` int(11) DEFAULT NULL,
  `days` int(11) DEFAULT NULL,
  `texture_uuid_soldout` varchar(36) NOT NULL,
  `texture_uuid_instock_small` varchar(36) NOT NULL,
  `texture_uuid_instock_selected` varchar(36) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `region` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `rental` (
  `id` int(11) NOT NULL,
  `avatarlink` int(11) NOT NULL,
  `streamlink` int(11) NOT NULL,
  `packagelink` int(11) NOT NULL,
  `noticelink` int(11) NOT NULL,
  `startunixtime` int(11) NOT NULL,
  `expireunixtime` int(11) NOT NULL,
  `renewals` tinyint(4) NOT NULL DEFAULT 0,
  `totalamount` int(11) NOT NULL DEFAULT 0,
  `message` text DEFAULT NULL,
  `rental_uid` varchar(8) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `reseller` (
  `id` int(11) NOT NULL,
  `avatarlink` int(11) NOT NULL,
  `allowed` tinyint(1) NOT NULL DEFAULT 0,
  `rate` int(3) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `server` (
  `id` int(11) NOT NULL,
  `domain` varchar(100) NOT NULL,
  `controlpanel_url` text NOT NULL,
  `apilink` int(11) NOT NULL DEFAULT 1,
  `api_username` text DEFAULT NULL,
  `api_password` text DEFAULT NULL,
  `opt_password_reset` tinyint(1) NOT NULL DEFAULT 1,
  `opt_autodj_next` tinyint(1) NOT NULL DEFAULT 1,
  `opt_toggle_autodj` tinyint(1) NOT NULL DEFAULT 1,
  `event_enable_start` tinyint(1) NOT NULL DEFAULT 1,
  `event_enable_renew` tinyint(1) NOT NULL DEFAULT 1,
  `event_disable_expire` tinyint(1) NOT NULL DEFAULT 0,
  `event_disable_revoke` tinyint(1) NOT NULL DEFAULT 1,
  `event_reset_password_revoke` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `slconfig` (
  `id` int(11) NOT NULL,
  `db_version` varchar(12) NOT NULL DEFAULT 'install',
  `new_resellers` tinyint(1) NOT NULL DEFAULT 0,
  `new_resellers_rate` int(3) NOT NULL DEFAULT 0,
  `sllinkcode` varchar(10) NOT NULL,
  `clients_list_mode` tinyint(1) NOT NULL DEFAULT 0,
  `publiclinkcode` varchar(12) DEFAULT NULL,
  `owner_av` int(11) NOT NULL,
  `eventstorage` tinyint(1) NOT NULL DEFAULT 0,
  `datatable_itemsperpage` int(3) NOT NULL DEFAULT 10,
  `http_inbound_secret` text NOT NULL,
  `smtp_host` text DEFAULT NULL,
  `smtp_port` int(11) DEFAULT NULL,
  `smtp_username` text DEFAULT NULL,
  `smtp_accesscode` text DEFAULT NULL,
  `smtp_from` text DEFAULT NULL,
  `smtp_replyto` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `slconfig` (`id`, `db_version`, `new_resellers`, `new_resellers_rate`, `sllinkcode`, `clients_list_mode`, `publiclinkcode`, `owner_av`, `eventstorage`, `datatable_itemsperpage`, `http_inbound_secret`, `smtp_host`, `smtp_port`, `smtp_username`, `smtp_accesscode`, `smtp_from`, `smtp_replyto`) VALUES
(1, '1.0.0.8', 0, 0, 'asdasdasd', 0, NULL, 1, 0, 10, '', NULL, NULL, NULL, NULL, NULL, NULL);

CREATE TABLE `staff` (
  `id` int(11) NOT NULL,
  `username` varchar(40) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `email_reset_code` varchar(8) DEFAULT NULL,
  `email_reset_expires` int(11) NOT NULL DEFAULT 0,
  `avatarlink` int(11) NOT NULL,
  `phash` varchar(64) NOT NULL,
  `lhash` varchar(64) NOT NULL,
  `psalt` varchar(64) NOT NULL,
  `ownerlevel` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `staff` (`id`, `username`, `email`, `email_reset_code`, `email_reset_expires`, `avatarlink`, `phash`, `lhash`, `psalt`, `ownerlevel`) VALUES
(1, 'Madpeter', NULL, NULL, 1585832870, 1, '876138b3b30082989dc3f61f607c5ba0a3adceaace', 'ea8acdc5deff970b901ccd2ee3ff60326bc746fcf3', '1063b99b60639e90d9cfc2ae1abd38e783ee90b891', 1);

CREATE TABLE `stream` (
  `id` int(11) NOT NULL,
  `serverlink` int(11) NOT NULL,
  `rentallink` int(11) DEFAULT NULL,
  `packagelink` int(11) NOT NULL,
  `port` int(5) NOT NULL,
  `needwork` tinyint(1) NOT NULL DEFAULT 0,
  `adminusername` text NOT NULL,
  `adminpassword` text NOT NULL,
  `djpassword` text NOT NULL,
  `stream_uid` varchar(8) NOT NULL,
  `mountpoint` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `template` (
  `id` int(11) NOT NULL,
  `name` varchar(30) NOT NULL,
  `detail` varchar(800) NOT NULL,
  `notecarddetail` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `template` (`id`, `name`, `detail`, `notecarddetail`) VALUES
(1, 'No mountpoint', 'Package: [[PACKAGE_NAME]][[NL]]\r\nListeners: [[PACKAGE_LISTENERS][[NL]]\r\nBitrate: [[PACKAGE_BITRATE]]kbps[[NL]]\r\nAutoDJ: [[PACKAGE_AUTODJ]] [[PACKAGE_AUTODJ_SIZE]]gb[[NL]]\r\n[[NL]]\r\nControl panel: [[SERVER_CONTROLPANEL]][[NL]]\r\nDomain: [[SERVER_DOMAIN]][[NL]]\r\nport: [[STREAM_PORT]][[NL]]\r\n[[NL]]\r\nAdmin user: [[STREAM_ADMINUSERNAME]][[NL]]\r\nAdmin pass: [[STREAM_ADMINPASSWORD]][[NL]]\r\nDJ pass: [[STREAM_DJPASSWORD]][[NL]]\r\n[[NL]]\r\nExpires: [[RENTAL_EXPIRES_DATETIME]]', 'Package: [[PACKAGE_NAME]][[NL]] \r\nListeners: [[PACKAGE_LISTENERS][[NL]] \r\nBitrate: [[PACKAGE_BITRATE]]kbps[[NL]] \r\nAutoDJ: [[PACKAGE_AUTODJ]] [[PACKAGE_AUTODJ_SIZE]]gb[[NL]] \r\n[[NL]] \r\nControl panel: [[SERVER_CONTROLPANEL]][[NL]] \r\nDomain: [[SERVER_DOMAIN]][[NL]] \r\nport: [[STREAM_PORT]][[NL]] [[NL]] \r\nAdmin user: [[STREAM_ADMINUSERNAME]][[NL]] \r\nAdmin pass: [[STREAM_ADMINPASSWORD]][[NL]] \r\nDJ pass: [[STREAM_DJPASSWORD]][[NL]] \r\n[[NL]] \r\nExpires: [[RENTAL_EXPIRES_DATETIME]]'),
(2, 'With mountpoint', 'Package: [[PACKAGE_NAME]][[NL]]\r\nListeners: [[PACKAGE_LISTENERS][[NL]]\r\nBitrate: [[PACKAGE_BITRATE]]kbps[[NL]]\r\nAutoDJ: [[PACKAGE_AUTODJ]] [[PACKAGE_AUTODJ_SIZE]]gb[[NL]]\r\n[[NL]]\r\nControl panel: [[SERVER_CONTROLPANEL]][[NL]]\r\nDomain: [[SERVER_DOMAIN]][[NL]]\r\nport: [[STREAM_PORT]][[NL]]\r\n[[NL]]\r\nAdmin user: [[STREAM_ADMINUSERNAME]][[NL]]\r\nAdmin pass: [[STREAM_ADMINPASSWORD]][[NL]]\r\nDJ pass: [[STREAM_DJPASSWORD]][[NL]]\r\nMountpoint: [[STREAM_MOUNTPOINT]][[NL]]\r\n[[NL]]\r\nExpires: [[RENTAL_EXPIRES_DATETIME]]', 'Package: [[PACKAGE_NAME]][[NL]] \r\nListeners: [[PACKAGE_LISTENERS][[NL]] \r\nBitrate: [[PACKAGE_BITRATE]]kbps[[NL]] \r\nAutoDJ: [[PACKAGE_AUTODJ]] [[PACKAGE_AUTODJ_SIZE]]gb[[NL]]\r\n[[NL]] \r\nControl panel: [[SERVER_CONTROLPANEL]][[NL]] \r\nDomain: [[SERVER_DOMAIN]][[NL]] \r\nport: [[STREAM_PORT]][[NL]] \r\n[[NL]] \r\nAdmin user: [[STREAM_ADMINUSERNAME]][[NL]] \r\nAdmin pass: [[STREAM_ADMINPASSWORD]][[NL]] \r\nDJ pass: [[STREAM_DJPASSWORD]][[NL]] \r\nMountpoint: [[STREAM_MOUNTPOINT]][[NL]] \r\n[[NL]] \r\nExpires: [[RENTAL_EXPIRES_DATETIME]]'),
(3, 'asdasd', 'asdasdasd', 'asdasdasd');

CREATE TABLE `textureconfig` (
  `id` int(11) NOT NULL,
  `name` text NOT NULL,
  `offline` varchar(36) NOT NULL,
  `wait_owner` varchar(36) NOT NULL,
  `stock_levels` varchar(36) NOT NULL,
  `make_payment` varchar(36) NOT NULL,
  `inuse` varchar(36) NOT NULL,
  `renew_here` varchar(36) NOT NULL,
  `treevend_waiting` varchar(36) NOT NULL,
  `proxyrenew` varchar(36) NOT NULL,
  `getting_details` varchar(36) NOT NULL,
  `request_details` varchar(36) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `textureconfig` (`id`, `name`, `offline`, `wait_owner`, `stock_levels`, `make_payment`, `inuse`, `renew_here`, `treevend_waiting`, `proxyrenew`, `getting_details`, `request_details`) VALUES
(1, 'asdasda', '9a92d601-5464-c5a2-6f19-cbcc12e40ac5', '9a92d601-5464-c5a2-6f19-cbcc12e40ac5', '9a92d601-5464-c5a2-6f19-cbcc12e40ac5', '9a92d601-5464-c5a2-6f19-cbcc12e40ac5', '9a92d601-5464-c5a2-6f19-cbcc12e40ac5', '9a92d601-5464-c5a2-6f19-cbcc12e40ac5', '9a92d601-5464-c5a2-6f19-cbcc12e40ac5', '9a92d601-5464-c5a2-6f19-cbcc12e40ac5', '9a92d601-5464-c5a2-6f19-cbcc12e40ac5', '9a92d601-5464-c5a2-6f19-cbcc12e40ac5');

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `avatarlink` int(11) NOT NULL,
  `packagelink` int(11) DEFAULT NULL,
  `streamlink` int(11) DEFAULT NULL,
  `resellerlink` int(11) DEFAULT NULL,
  `regionlink` int(11) DEFAULT NULL,
  `amount` int(11) NOT NULL,
  `unixtime` int(11) NOT NULL,
  `transaction_uid` varchar(8) NOT NULL,
  `renew` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `treevender` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `treevender_packages` (
  `id` int(11) NOT NULL,
  `treevenderlink` int(11) NOT NULL,
  `packagelink` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


ALTER TABLE `apis`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `api_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `serverlink` (`serverlink`),
  ADD KEY `rentallink` (`rentallink`),
  ADD KEY `streamlink` (`streamlink`);

ALTER TABLE `avatar`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `avataruuid` (`avataruuid`),
  ADD UNIQUE KEY `avatar_uid` (`avatar_uid`);

ALTER TABLE `banlist`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `avatar_link` (`avatar_link`);

ALTER TABLE `botconfig`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `avatarlink` (`avatarlink`);

ALTER TABLE `detail`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `rentallink` (`rentallink`);

ALTER TABLE `event`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `message`
  ADD PRIMARY KEY (`id`),
  ADD KEY `avatarlink` (`avatarlink`);

ALTER TABLE `notecard`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `rentallink` (`rentallink`),
  ADD KEY `noticelink` (`noticelink`);

ALTER TABLE `notice`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `hoursremaining` (`hoursremaining`);

ALTER TABLE `objects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `avatarlink` (`avatarlink`),
  ADD KEY `regionlink` (`regionlink`);

ALTER TABLE `package`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `package_uid` (`package_uid`),
  ADD UNIQUE KEY `name` (`name`),
  ADD KEY `templatelink` (`templatelink`);

ALTER TABLE `region`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

ALTER TABLE `rental`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `streamlink` (`streamlink`),
  ADD UNIQUE KEY `rental_uid` (`rental_uid`),
  ADD KEY `avatarlink` (`avatarlink`),
  ADD KEY `packagelink` (`packagelink`),
  ADD KEY `noticelink` (`noticelink`);

ALTER TABLE `reseller`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `avatarlink` (`avatarlink`) USING BTREE;

ALTER TABLE `server`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `domain` (`domain`),
  ADD KEY `apilink` (`apilink`);

ALTER TABLE `slconfig`
  ADD PRIMARY KEY (`id`),
  ADD KEY `owner_av` (`owner_av`);

ALTER TABLE `staff`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `phash` (`phash`),
  ADD UNIQUE KEY `lhash` (`lhash`),
  ADD UNIQUE KEY `psalt` (`psalt`),
  ADD UNIQUE KEY `avatarlink` (`avatarlink`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `email_reset_code` (`email_reset_code`);

ALTER TABLE `stream`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `stream_uid` (`stream_uid`),
  ADD KEY `packagelink` (`packagelink`),
  ADD KEY `rentallink` (`rentallink`),
  ADD KEY `serverlink` (`serverlink`) USING BTREE;

ALTER TABLE `template`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `textureconfig`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `transaction_uid` (`transaction_uid`),
  ADD KEY `avatarlink` (`avatarlink`),
  ADD KEY `packagelink` (`packagelink`),
  ADD KEY `streamlink` (`streamlink`),
  ADD KEY `resellerlink` (`resellerlink`),
  ADD KEY `regionlink` (`regionlink`);

ALTER TABLE `treevender`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

ALTER TABLE `treevender_packages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `treevenderlink` (`treevenderlink`),
  ADD KEY `packagelink` (`packagelink`) USING BTREE;


ALTER TABLE `apis`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

ALTER TABLE `avatar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `banlist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `botconfig`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `detail`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `event`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `message`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `notecard`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `notice`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

ALTER TABLE `objects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `package`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `region`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `rental`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `reseller`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `server`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `slconfig`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `staff`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

ALTER TABLE `stream`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `template`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

ALTER TABLE `textureconfig`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `treevender`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `treevender_packages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;


ALTER TABLE `api_requests`
  ADD CONSTRAINT `apirequest_rental_inuse` FOREIGN KEY (`rentallink`) REFERENCES `rental` (`id`) ON UPDATE NO ACTION,
  ADD CONSTRAINT `apirequest_server_inuse` FOREIGN KEY (`serverlink`) REFERENCES `server` (`id`) ON UPDATE NO ACTION,
  ADD CONSTRAINT `apirequest_stream_inuse` FOREIGN KEY (`streamlink`) REFERENCES `stream` (`id`) ON UPDATE NO ACTION;

ALTER TABLE `banlist`
  ADD CONSTRAINT `avatar_in_use_banlist` FOREIGN KEY (`avatar_link`) REFERENCES `avatar` (`id`) ON UPDATE NO ACTION;

ALTER TABLE `botconfig`
  ADD CONSTRAINT `botconfig_ibfk_1` FOREIGN KEY (`avatarlink`) REFERENCES `avatar` (`id`) ON UPDATE NO ACTION;

ALTER TABLE `detail`
  ADD CONSTRAINT `detail_ibfk_1` FOREIGN KEY (`rentallink`) REFERENCES `rental` (`id`) ON UPDATE NO ACTION;

ALTER TABLE `message`
  ADD CONSTRAINT `message_ibfk_1` FOREIGN KEY (`avatarlink`) REFERENCES `avatar` (`id`) ON UPDATE NO ACTION;

ALTER TABLE `notecard`
  ADD CONSTRAINT `notecard_ibfk_1` FOREIGN KEY (`rentallink`) REFERENCES `rental` (`id`) ON UPDATE NO ACTION,
  ADD CONSTRAINT `notecard_ibfk_2` FOREIGN KEY (`noticelink`) REFERENCES `notice` (`id`) ON UPDATE NO ACTION;

ALTER TABLE `objects`
  ADD CONSTRAINT `objects_ibfk_1` FOREIGN KEY (`regionlink`) REFERENCES `region` (`id`) ON UPDATE NO ACTION,
  ADD CONSTRAINT `objects_ibfk_2` FOREIGN KEY (`avatarlink`) REFERENCES `avatar` (`id`) ON UPDATE NO ACTION;

ALTER TABLE `package`
  ADD CONSTRAINT `package_ibfk_1` FOREIGN KEY (`templatelink`) REFERENCES `template` (`id`) ON UPDATE NO ACTION;

ALTER TABLE `rental`
  ADD CONSTRAINT `rental_ibfk_1` FOREIGN KEY (`streamlink`) REFERENCES `stream` (`id`) ON UPDATE NO ACTION,
  ADD CONSTRAINT `rental_ibfk_2` FOREIGN KEY (`avatarlink`) REFERENCES `avatar` (`id`) ON UPDATE NO ACTION,
  ADD CONSTRAINT `rental_ibfk_3` FOREIGN KEY (`noticelink`) REFERENCES `notice` (`id`) ON UPDATE NO ACTION;

ALTER TABLE `reseller`
  ADD CONSTRAINT `reseller_ibfk_1` FOREIGN KEY (`avatarlink`) REFERENCES `avatar` (`id`) ON UPDATE NO ACTION;

ALTER TABLE `server`
  ADD CONSTRAINT `server_api_inuse` FOREIGN KEY (`apilink`) REFERENCES `apis` (`id`) ON UPDATE NO ACTION;

ALTER TABLE `slconfig`
  ADD CONSTRAINT `slconfig_ibfk_1` FOREIGN KEY (`owner_av`) REFERENCES `avatar` (`id`) ON UPDATE NO ACTION;

ALTER TABLE `staff`
  ADD CONSTRAINT `staff_ibfk_1` FOREIGN KEY (`avatarlink`) REFERENCES `avatar` (`id`) ON UPDATE NO ACTION;

ALTER TABLE `stream`
  ADD CONSTRAINT `stream_ibfk_1` FOREIGN KEY (`rentallink`) REFERENCES `rental` (`id`) ON UPDATE NO ACTION,
  ADD CONSTRAINT `stream_ibfk_2` FOREIGN KEY (`packagelink`) REFERENCES `package` (`id`) ON UPDATE NO ACTION,
  ADD CONSTRAINT `stream_ibfk_3` FOREIGN KEY (`serverlink`) REFERENCES `server` (`id`) ON UPDATE NO ACTION;

ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`avatarlink`) REFERENCES `avatar` (`id`) ON UPDATE NO ACTION,
  ADD CONSTRAINT `transactions_ibfk_2` FOREIGN KEY (`packagelink`) REFERENCES `package` (`id`) ON UPDATE NO ACTION,
  ADD CONSTRAINT `transactions_ibfk_3` FOREIGN KEY (`streamlink`) REFERENCES `stream` (`id`) ON UPDATE NO ACTION,
  ADD CONSTRAINT `transactions_ibfk_4` FOREIGN KEY (`regionlink`) REFERENCES `region` (`id`) ON UPDATE NO ACTION,
  ADD CONSTRAINT `transactions_ibfk_5` FOREIGN KEY (`resellerlink`) REFERENCES `reseller` (`id`) ON UPDATE NO ACTION;

ALTER TABLE `treevender_packages`
  ADD CONSTRAINT `treevender_packages_ibfk_1` FOREIGN KEY (`treevenderlink`) REFERENCES `treevender` (`id`) ON UPDATE NO ACTION,
  ADD CONSTRAINT `treevender_packages_ibfk_2` FOREIGN KEY (`packagelink`) REFERENCES `package` (`id`) ON UPDATE NO ACTION;
