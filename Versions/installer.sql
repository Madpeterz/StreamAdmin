SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

CREATE TABLE `auditlog` (
  `id` int(11) NOT NULL,
  `store` varchar(12) NOT NULL,
  `sourceid` varchar(8) DEFAULT NULL,
  `valuename` text NOT NULL,
  `oldvalue` text DEFAULT NULL,
  `newvalue` text DEFAULT NULL,
  `unixtime` int(11) NOT NULL,
  `avatarLink` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `avatar` (
  `id` int(11) NOT NULL,
  `avatarUUID` varchar(36) NOT NULL,
  `avatarName` text NOT NULL,
  `avatarUid` varchar(8) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `avatar` (`id`, `avatarUUID`, `avatarName`, `avatarUid`) VALUES
(1, '00000000-0000-0000-0000-000000000000', 'System', 'System'),
(2, '289c3e36-69b3-40c5-9229-0c6a5d230766', 'Madpeter Zond', 'SysDevOp');

CREATE TABLE `banlist` (
  `id` int(11) NOT NULL,
  `avatarLink` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `botcommandq` (
  `id` int(11) NOT NULL,
  `command` text NOT NULL,
  `args` text CHARACTER SET utf8mb4 DEFAULT NULL,
  `unixtime` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `botconfig` (
  `id` int(11) NOT NULL,
  `avatarLink` int(11) NOT NULL,
  `secret` text DEFAULT NULL,
  `notecards` tinyint(1) NOT NULL DEFAULT 0,
  `ims` tinyint(1) NOT NULL DEFAULT 0,
  `invites` tinyint(1) NOT NULL DEFAULT 0,
  `inviteGroupUUID` varchar(36) DEFAULT NULL,
  `httpMode` tinyint(1) NOT NULL DEFAULT 0,
  `httpURL` text DEFAULT NULL,
  `httpToken` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `botconfig` (`id`, `avatarLink`, `secret`, `notecards`, `ims`, `invites`, `inviteGroupUUID`, `httpMode`, `httpURL`, `httpToken`) VALUES
(1, 1, 'Signed command code', 0, 0, 0, NULL, 0, NULL, NULL);

CREATE TABLE `datatable` (
  `id` int(11) NOT NULL,
  `hideColZero` tinyint(1) NOT NULL DEFAULT 1,
  `col` int(11) NOT NULL DEFAULT 0,
  `cols` text NOT NULL,
  `name` text NOT NULL,
  `dir` text NOT NULL DEFAULT 'desc'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `datatable` (`id`, `hideColZero`, `col`, `cols`, `name`, `dir`) VALUES
(1, 1, 0, '0=id,1=region,3=Percentage,4=Count Up,5=Count down', 'Health', 'desc'),
(2, 1, 1, '0=id,1=Object,2=Last seen,5=Owner', 'Health / Detailed', 'desc'),
(3, 1, 0, '0=id,1=Rental UID,2=Avatar,3=Port,5=Timeleft,6=Status,7=Renewals', 'Client / List', 'desc'),
(4, 1, 0, '0=id,1=Stream UID,2=Server,3=Port', 'Stream / List', 'desc'),
(5, 1, 0, '0=id,1=Package name,2=Sold,3=Need work,4=Ready', 'Streams / Package menu', 'desc'),
(6, 1, 0, '0=id,1=Package UID,2=Name,4=Listeners,5=Days,6=Kbps,7=Cost', 'Packages / List', 'desc');

CREATE TABLE `detail` (
  `id` int(11) NOT NULL,
  `rentalLink` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `eventsq` (
  `id` int(11) NOT NULL,
  `eventName` text NOT NULL,
  `eventMessage` text NOT NULL,
  `eventUnixtime` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `message` (
  `id` int(11) NOT NULL,
  `avatarLink` int(11) NOT NULL,
  `message` varchar(900) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `notecard` (
  `id` int(11) NOT NULL,
  `rentalLink` int(11) NOT NULL,
  `asNotice` tinyint(1) NOT NULL DEFAULT 0,
  `noticeLink` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `notecardmail` (
  `id` int(11) NOT NULL,
  `avatarLink` int(11) NOT NULL,
  `noticenotecardLink` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `notice` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `imMessage` varchar(800) NOT NULL,
  `sendObjectIM` tinyint(1) NOT NULL DEFAULT 1,
  `useBot` tinyint(1) NOT NULL DEFAULT 0,
  `sendNotecard` tinyint(1) NOT NULL DEFAULT 0,
  `notecardDetail` text NOT NULL,
  `hoursRemaining` int(11) NOT NULL DEFAULT 0,
  `noticeNotecardLink` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `notice` (`id`, `name`, `imMessage`, `sendObjectIM`, `useBot`, `sendNotecard`, `notecardDetail`, `hoursRemaining`, `noticeNotecardLink`) VALUES
(1, '7 day notice', 'Hello [[AVATAR_FIRSTNAME]] your stream on [[SERVER_DOMAIN]] port [[STREAM_PORT]] now has [[RENTAL_TIMELEFT]] remaining', 1, 1, 0, '', 168, 1),
(2, '5 day notice', 'Hello [[AVATAR_FIRSTNAME]] your stream on [[SERVER_DOMAIN]] port [[STREAM_PORT]] now has [[RENTAL_TIMELEFT]]  remaining, When you have time please drop into our store.', 1, 1, 0, '', 120, 1),
(3, '3 day notice', 'Hello [[AVATAR_FIRSTNAME]] your stream on [[SERVER_DOMAIN]] port [[STREAM_PORT]] now has [[RENTAL_TIMELEFT]] remaining, Dont forget to renew your service!', 1, 1, 0, '', 72, 1),
(4, '1 day notice', 'Hello [[AVATAR_FIRSTNAME]] your stream on [[SERVER_DOMAIN]] port [[STREAM_PORT]] now has less than 24 hours remaining. Please renew to avoid loss of service.', 1, 1, 0, '', 24, 1),
(5, '5 hour notice', 'Hello [[AVATAR_FIRSTNAME]] your stream on [[SERVER_DOMAIN]] port [[STREAM_PORT]] now has less than 5 hours remaining. ', 1, 1, 0, '', 5, 1),
(6, 'Expired', 'Hello [[AVATAR_FIRSTNAME]] your stream on [[SERVER_DOMAIN]] port [[STREAM_PORT]]  has now expired please renew asap or risk losing the assigned port.', 1, 1, 0, '', 0, 1),
(10, 'Active', '', 1, 0, 0, '', 999, 1);

CREATE TABLE `noticenotecard` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `missing` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `noticenotecard` (`id`, `name`, `missing`) VALUES
(1, 'None', 0);

CREATE TABLE `objects` (
  `id` int(11) NOT NULL,
  `avatarLink` int(11) NOT NULL,
  `regionLink` int(11) NOT NULL,
  `objectUUID` varchar(36) NOT NULL,
  `objectName` text NOT NULL,
  `objectMode` varchar(30) NOT NULL,
  `objectXYZ` text NOT NULL,
  `lastSeen` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `package` (
  `id` int(11) NOT NULL,
  `packageUid` varchar(8) NOT NULL,
  `name` varchar(60) NOT NULL,
  `autodj` tinyint(1) NOT NULL DEFAULT 0,
  `autodjSize` text DEFAULT NULL,
  `listeners` int(11) DEFAULT NULL,
  `bitrate` int(11) DEFAULT NULL,
  `templateLink` int(11) DEFAULT NULL,
  `servertypeLink` int(11) NOT NULL DEFAULT 1,
  `cost` int(11) DEFAULT NULL,
  `days` int(11) DEFAULT NULL,
  `textureSoldout` varchar(36) NOT NULL,
  `textureInstockSmall` varchar(36) NOT NULL,
  `textureInstockSelected` varchar(36) NOT NULL,
  `welcomeNotecardLink` int(11) NOT NULL DEFAULT 1,
  `setupNotecardLink` int(11) NOT NULL DEFAULT 1,
  `enableGroupInvite` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `region` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `rental` (
  `id` int(11) NOT NULL,
  `avatarLink` int(11) NOT NULL,
  `streamLink` int(11) NOT NULL,
  `packageLink` int(11) NOT NULL,
  `noticeLink` int(11) NOT NULL,
  `startUnixtime` int(11) NOT NULL,
  `expireUnixtime` int(11) NOT NULL,
  `renewals` tinyint(4) NOT NULL DEFAULT 0,
  `totalAmount` int(11) NOT NULL DEFAULT 0,
  `message` text DEFAULT NULL,
  `rentalUid` varchar(8) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `rentalnoticeptout` (
  `id` int(11) NOT NULL,
  `rentalLink` int(11) NOT NULL,
  `noticeLink` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `reseller` (
  `id` int(11) NOT NULL,
  `avatarLink` int(11) NOT NULL,
  `allowed` tinyint(1) NOT NULL DEFAULT 0,
  `rate` int(3) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `server` (
  `id` int(11) NOT NULL,
  `domain` varchar(100) NOT NULL,
  `controlPanelURL` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `servertypes` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `servertypes` (`id`, `name`) VALUES
(3, 'Icecast'),
(1, 'ShoutcastV1'),
(2, 'ShoutcastV2');

CREATE TABLE `slconfig` (
  `id` int(11) NOT NULL,
  `dbVersion` varchar(12) NOT NULL DEFAULT 'install',
  `newResellers` tinyint(1) NOT NULL DEFAULT 0,
  `newResellersRate` int(3) NOT NULL DEFAULT 0,
  `slLinkCode` varchar(10) NOT NULL,
  `clientsListMode` tinyint(1) NOT NULL DEFAULT 0,
  `publicLinkCode` varchar(12) DEFAULT NULL,
  `hudLinkCode` varchar(12) DEFAULT NULL,
  `ownerAvatarLink` int(11) NOT NULL,
  `datatableItemsPerPage` int(3) NOT NULL DEFAULT 10,
  `httpInboundSecret` text NOT NULL,
  `displayTimezoneLink` int(11) NOT NULL DEFAULT 11,
  `hudAllowDiscord` tinyint(1) NOT NULL DEFAULT 0,
  `hudDiscordLink` text DEFAULT 'Not setup yet',
  `hudAllowGroup` tinyint(1) NOT NULL DEFAULT 0,
  `hudGroupLink` text DEFAULT 'Not setup yet',
  `hudAllowDetails` tinyint(1) NOT NULL DEFAULT 0,
  `hudAllowRenewal` tinyint(1) NOT NULL DEFAULT 0,
  `eventsAPI` tinyint(1) NOT NULL DEFAULT 0,
  `paymentKey` text DEFAULT NULL,
  `streamListOption` int(11) NOT NULL DEFAULT 1,
  `clientsDisplayServer` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `slconfig` (`id`, `dbVersion`, `newResellers`, `newResellersRate`, `slLinkCode`, `clientsListMode`, `publicLinkCode`, `hudLinkCode`, `ownerAvatarLink`, `datatableItemsPerPage`, `httpInboundSecret`, `displayTimezoneLink`, `hudAllowDiscord`, `hudDiscordLink`, `hudAllowGroup`, `hudGroupLink`, `hudAllowDetails`, `hudAllowRenewal`, `eventsAPI`, `paymentKey`, `streamListOption`, `clientsDisplayServer`) VALUES
(1, '2.0.0.0', 0, 0, 'install', 1, 'install', 'install', 1, 10, 'install', 11, 0, 'Not setup yet', 0, 'Not setup yet', 0, 0, 0, 'install', 1, 1);

CREATE TABLE `staff` (
  `id` int(11) NOT NULL,
  `username` varchar(40) NOT NULL,
  `emailResetCode` varchar(8) DEFAULT NULL,
  `emailResetExpires` int(11) NOT NULL DEFAULT 0,
  `avatarLink` int(11) NOT NULL,
  `phash` varchar(64) NOT NULL,
  `lhash` varchar(64) NOT NULL,
  `psalt` varchar(64) NOT NULL,
  `ownerLevel` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `staff` (`id`, `username`, `emailResetCode`, `emailResetExpires`, `avatarLink`, `phash`, `lhash`, `psalt`, `ownerLevel`) VALUES
(1, 'Install', NULL, 0, 1, 'Install', 'Install', 'Install', 1);

CREATE TABLE `stream` (
  `id` int(11) NOT NULL,
  `serverLink` int(11) NOT NULL,
  `rentalLink` int(11) DEFAULT NULL,
  `packageLink` int(11) NOT NULL,
  `port` int(5) NOT NULL,
  `needWork` tinyint(1) NOT NULL DEFAULT 0,
  `adminUsername` text NOT NULL,
  `adminPassword` text NOT NULL,
  `djPassword` text NOT NULL,
  `streamUid` varchar(8) NOT NULL,
  `mountpoint` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `template` (
  `id` int(11) NOT NULL,
  `name` varchar(30) NOT NULL,
  `detail` varchar(800) NOT NULL,
  `notecardDetail` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `template` (`id`, `name`, `detail`, `notecardDetail`) VALUES
(1, 'Shoutcast', 'Package: [[PACKAGE_NAME]][[NL]]\r\nListeners: [[PACKAGE_LISTENERS]][[NL]]\r\nBitrate: [[PACKAGE_BITRATE]]kbps[[NL]]\r\nAutoDJ: [[PACKAGE_AUTODJ]] [[PACKAGE_AUTODJ_SIZE]]gb[[NL]]\r\n[[NL]]\r\nControl panel: [[SERVER_CONTROLPANEL]][[NL]]\r\nDomain: [[SERVER_DOMAIN]][[NL]]\r\nport: [[STREAM_PORT]][[NL]]\r\n[[NL]]\r\nAdmin user: [[STREAM_ADMINUSERNAME]][[NL]]\r\nAdmin pass: [[STREAM_ADMINPASSWORD]][[NL]]\r\nDJ pass: [[STREAM_DJPASSWORD]][[NL]]\r\n[[NL]]\r\nExpires: [[RENTAL_EXPIRES_DATETIME]]', 'Package: [[PACKAGE_NAME]][[NL]] \r\nListeners: [[PACKAGE_LISTENERS]][[NL]] \r\nBitrate: [[PACKAGE_BITRATE]]kbps[[NL]] \r\nAutoDJ: [[PACKAGE_AUTODJ]] [[PACKAGE_AUTODJ_SIZE]]gb[[NL]] \r\n[[NL]] \r\nControl panel: [[SERVER_CONTROLPANEL]][[NL]] \r\nDomain: [[SERVER_DOMAIN]][[NL]] \r\nport: [[STREAM_PORT]][[NL]] [[NL]] \r\nAdmin user: [[STREAM_ADMINUSERNAME]][[NL]] \r\nAdmin pass: [[STREAM_ADMINPASSWORD]][[NL]] \r\nDJ pass: [[STREAM_DJPASSWORD]][[NL]] \r\n[[NL]] \r\nExpires: [[RENTAL_EXPIRES_DATETIME]]'),
(2, 'Icecast', 'Package: [[PACKAGE_NAME]][[NL]]\r\nListeners: [[PACKAGE_LISTENERS]][[NL]]\r\nBitrate: [[PACKAGE_BITRATE]]kbps[[NL]]\r\nAutoDJ: [[PACKAGE_AUTODJ]] [[PACKAGE_AUTODJ_SIZE]]gb[[NL]]\r\n[[NL]]\r\nControl panel: [[SERVER_CONTROLPANEL]][[NL]]\r\nDomain: [[SERVER_DOMAIN]][[NL]]\r\nport: [[STREAM_PORT]][[NL]]\r\n[[NL]]\r\nAdmin user: [[STREAM_ADMINUSERNAME]][[NL]]\r\nAdmin pass: [[STREAM_ADMINPASSWORD]][[NL]]\r\nDJ pass: [[STREAM_DJPASSWORD]][[NL]]\r\nMountpoint: [[STREAM_MOUNTPOINT]][[NL]]\r\n[[NL]]\r\nExpires: [[RENTAL_EXPIRES_DATETIME]]', 'Package: [[PACKAGE_NAME]][[NL]] \r\nListeners: [[PACKAGE_LISTENERS]][[NL]] \r\nBitrate: [[PACKAGE_BITRATE]]kbps[[NL]] \r\nAutoDJ: [[PACKAGE_AUTODJ]] [[PACKAGE_AUTODJ_SIZE]]gb[[NL]]\r\n[[NL]] \r\nControl panel: [[SERVER_CONTROLPANEL]][[NL]] \r\nDomain: [[SERVER_DOMAIN]][[NL]] \r\nport: [[STREAM_PORT]][[NL]] \r\n[[NL]] \r\nAdmin user: [[STREAM_ADMINUSERNAME]][[NL]] \r\nAdmin pass: [[STREAM_ADMINPASSWORD]][[NL]] \r\nDJ pass: [[STREAM_DJPASSWORD]][[NL]] \r\nMountpoint: [[STREAM_MOUNTPOINT]][[NL]] \r\n[[NL]] \r\nExpires: [[RENTAL_EXPIRES_DATETIME]]');

CREATE TABLE `textureconfig` (
  `id` int(11) NOT NULL,
  `name` text NOT NULL,
  `offline` varchar(36) NOT NULL,
  `waitOwner` varchar(36) NOT NULL,
  `stockLevels` varchar(36) NOT NULL,
  `makePayment` varchar(36) NOT NULL,
  `inUse` varchar(36) NOT NULL,
  `renewHere` varchar(36) NOT NULL,
  `proxyRenew` varchar(36) NOT NULL,
  `gettingDetails` varchar(36) NOT NULL,
  `requestDetails` varchar(36) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `textureconfig` (`id`, `name`, `offline`, `waitOwner`, `stockLevels`, `makePayment`, `inUse`, `renewHere`, `proxyRenew`, `gettingDetails`, `requestDetails`) VALUES
(1, 'SA7 defaults', '718fdaf8-df99-5c7f-48fb-feb94db12675', '51d5f381-43cd-84f0-c226-f9f89c12af7e', '257c594e-41d8-53d8-5280-5329a259a5d8', '19e57cf0-254f-32d7-fc9f-0d698aca4dc2', '10b68027-7e7f-fbbc-0c9f-6afabbfc636c', '0e99005c-526e-468c-7c0c-2569096f6162', 'cc1c1124-b5d0-595b-12b6-016c61b82456', 'bc14cd11-edca-4bd2-3a21-46d870966edd', 'c724a9ea-ee79-6d80-3249-ff016de063b0');

CREATE TABLE `timezones` (
  `id` int(11) NOT NULL,
  `name` varchar(125) NOT NULL,
  `code` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `timezones` (`id`, `name`, `code`) VALUES
(1, 'United States / Eastern', 'America/New_York'),
(2, 'United States / Central', 'America/Chicago'),
(3, 'United States / Mountain', 'America/Denver'),
(4, 'United States / Mountain [No DST]', 'America/Phoenix'),
(5, 'United States / Pacific', 'America/Los_Angeles'),
(6, 'United States / Alaska', 'America/Anchorage'),
(7, 'United States / Hawaii', 'America/Adak'),
(8, 'United States / Hawaii [No DST]', 'Pacific/Honolulu'),
(9, 'Europe / Dublin', 'Europe/Dublin'),
(10, 'Europe / Paris', 'Europe/Paris'),
(11, 'Europe / London', 'Europe/London');

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `avatarLink` int(11) NOT NULL,
  `packageLink` int(11) DEFAULT NULL,
  `streamLink` int(11) DEFAULT NULL,
  `resellerLink` int(11) DEFAULT NULL,
  `regionLink` int(11) DEFAULT NULL,
  `amount` int(11) NOT NULL,
  `unixtime` int(11) NOT NULL,
  `transactionUid` varchar(8) NOT NULL,
  `renew` tinyint(1) NOT NULL DEFAULT 0,
  `SLtransactionUUID` varchar(36) DEFAULT NULL,
  `ViaHud` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `treevender` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `textureWaiting` varchar(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000',
  `textureInuse` varchar(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000',
  `hideSoldout` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `treevenderpackages` (
  `id` int(11) NOT NULL,
  `treevenderLink` int(11) NOT NULL,
  `packageLink` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


ALTER TABLE `auditlog`
  ADD PRIMARY KEY (`id`),
  ADD KEY `staffLink` (`avatarLink`);

ALTER TABLE `avatar`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `avataruuid` (`avatarUUID`),
  ADD UNIQUE KEY `avatar_uid` (`avatarUid`);

ALTER TABLE `banlist`
  ADD PRIMARY KEY (`id`),
  ADD KEY `avatarLink` (`avatarLink`);

ALTER TABLE `botcommandq`
  ADD PRIMARY KEY (`id`),
  ADD KEY `unixtime` (`unixtime`);

ALTER TABLE `botconfig`
  ADD PRIMARY KEY (`id`),
  ADD KEY `avatarLink` (`avatarLink`);

ALTER TABLE `datatable`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `detail`
  ADD PRIMARY KEY (`id`),
  ADD KEY `rentalLink` (`rentalLink`);

ALTER TABLE `eventsq`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `message`
  ADD PRIMARY KEY (`id`),
  ADD KEY `avatarLink` (`avatarLink`);

ALTER TABLE `notecard`
  ADD PRIMARY KEY (`id`),
  ADD KEY `noticeLink` (`noticeLink`),
  ADD KEY `rentalLink` (`rentalLink`);

ALTER TABLE `notecardmail`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notecardmail_avatar_inuse` (`avatarLink`),
  ADD KEY `notecardmial_notecard_inuse` (`noticenotecardLink`);

ALTER TABLE `notice`
  ADD PRIMARY KEY (`id`),
  ADD KEY `noticeNotecardLink` (`noticeNotecardLink`);

ALTER TABLE `noticenotecard`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

ALTER TABLE `objects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `avatarLink` (`avatarLink`),
  ADD KEY `regionLink` (`regionLink`);

ALTER TABLE `package`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD UNIQUE KEY `packageUid` (`packageUid`),
  ADD KEY `servertypeLink` (`servertypeLink`),
  ADD KEY `templateLink` (`templateLink`),
  ADD KEY `welcomeNotecardLink` (`welcomeNotecardLink`),
  ADD KEY `setupNotecardLink` (`setupNotecardLink`);

ALTER TABLE `region`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

ALTER TABLE `rental`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `streamLink` (`streamLink`),
  ADD UNIQUE KEY `rentalUid` (`rentalUid`),
  ADD KEY `avatarLink` (`avatarLink`),
  ADD KEY `packageLink` (`packageLink`),
  ADD KEY `noticeLink` (`noticeLink`);

ALTER TABLE `rentalnoticeptout`
  ADD PRIMARY KEY (`id`),
  ADD KEY `rentalLink` (`rentalLink`),
  ADD KEY `noticeLink` (`noticeLink`);

ALTER TABLE `reseller`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `avatarLink` (`avatarLink`);

ALTER TABLE `server`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `domain` (`domain`);

ALTER TABLE `servertypes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

ALTER TABLE `slconfig`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ownerAvatarLink` (`ownerAvatarLink`),
  ADD KEY `displayTimezoneLink` (`displayTimezoneLink`);

ALTER TABLE `staff`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `phash` (`phash`),
  ADD UNIQUE KEY `lhash` (`lhash`),
  ADD UNIQUE KEY `psalt` (`psalt`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `avatarLink` (`avatarLink`),
  ADD UNIQUE KEY `emailResetCode` (`emailResetCode`);

ALTER TABLE `stream`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `streamUid` (`streamUid`),
  ADD UNIQUE KEY `rentalLink` (`rentalLink`),
  ADD KEY `serverLink` (`serverLink`),
  ADD KEY `packageLink` (`packageLink`);

ALTER TABLE `template`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `textureconfig`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `timezones`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `transactionUid` (`transactionUid`),
  ADD UNIQUE KEY `SLtransactionUUID` (`SLtransactionUUID`),
  ADD KEY `avatarLink` (`avatarLink`),
  ADD KEY `packageLink` (`packageLink`),
  ADD KEY `streamLink` (`streamLink`),
  ADD KEY `resellerLink` (`resellerLink`),
  ADD KEY `regionLink` (`regionLink`);

ALTER TABLE `treevender`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

ALTER TABLE `treevenderpackages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `treevenderLink` (`treevenderLink`),
  ADD KEY `packageLink` (`packageLink`);


ALTER TABLE `auditlog`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `avatar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

ALTER TABLE `banlist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `botcommandq`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `botconfig`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `datatable`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

ALTER TABLE `detail`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `eventsq`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `message`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `notecard`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `notecardmail`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `notice`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

ALTER TABLE `noticenotecard`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `objects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `package`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `region`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `rental`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `rentalnoticeptout`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `reseller`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `server`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `servertypes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

ALTER TABLE `slconfig`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

ALTER TABLE `staff`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `stream`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `template`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

ALTER TABLE `textureconfig`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `timezones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `treevender`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `treevenderpackages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;


ALTER TABLE `auditlog`
  ADD CONSTRAINT `auditlog_staff_inuse` FOREIGN KEY (`avatarLink`) REFERENCES `avatar` (`id`) ON UPDATE NO ACTION;

ALTER TABLE `banlist`
  ADD CONSTRAINT `avatar_in_use_banlist` FOREIGN KEY (`avatarLink`) REFERENCES `avatar` (`id`) ON UPDATE NO ACTION;

ALTER TABLE `botconfig`
  ADD CONSTRAINT `avatar_in_use_botconfig` FOREIGN KEY (`avatarLink`) REFERENCES `avatar` (`id`) ON UPDATE NO ACTION;

ALTER TABLE `detail`
  ADD CONSTRAINT `rental_in_use_detail` FOREIGN KEY (`rentalLink`) REFERENCES `rental` (`id`) ON UPDATE NO ACTION;

ALTER TABLE `message`
  ADD CONSTRAINT `avatar_in_use_message` FOREIGN KEY (`avatarLink`) REFERENCES `avatar` (`id`) ON UPDATE NO ACTION;

ALTER TABLE `notecard`
  ADD CONSTRAINT `notice_in_use_notecard` FOREIGN KEY (`noticeLink`) REFERENCES `notice` (`id`) ON UPDATE NO ACTION,
  ADD CONSTRAINT `rental_in_use_notecard` FOREIGN KEY (`rentalLink`) REFERENCES `rental` (`id`) ON UPDATE NO ACTION;

ALTER TABLE `notecardmail`
  ADD CONSTRAINT `notecardmail_avatar_inuse` FOREIGN KEY (`avatarLink`) REFERENCES `avatar` (`id`) ON UPDATE NO ACTION,
  ADD CONSTRAINT `notecardmial_notecard_inuse` FOREIGN KEY (`noticenotecardLink`) REFERENCES `noticenotecard` (`id`) ON UPDATE NO ACTION;

ALTER TABLE `notice`
  ADD CONSTRAINT `noticenotcard_in_use_notice` FOREIGN KEY (`noticeNotecardLink`) REFERENCES `noticenotecard` (`id`) ON UPDATE NO ACTION;

ALTER TABLE `objects`
  ADD CONSTRAINT `avatar_in_use_objects` FOREIGN KEY (`avatarLink`) REFERENCES `avatar` (`id`) ON UPDATE NO ACTION,
  ADD CONSTRAINT `region_in_use_objects` FOREIGN KEY (`regionLink`) REFERENCES `region` (`id`) ON UPDATE NO ACTION;

ALTER TABLE `package`
  ADD CONSTRAINT `noticenotecard_in_use_package_1` FOREIGN KEY (`welcomeNotecardLink`) REFERENCES `noticenotecard` (`id`) ON UPDATE NO ACTION,
  ADD CONSTRAINT `noticenotecard_in_use_package_2` FOREIGN KEY (`setupNotecardLink`) REFERENCES `noticenotecard` (`id`) ON UPDATE NO ACTION,
  ADD CONSTRAINT `servertype_in_use_package` FOREIGN KEY (`servertypeLink`) REFERENCES `servertypes` (`id`) ON UPDATE NO ACTION,
  ADD CONSTRAINT `template_in_use_package` FOREIGN KEY (`templateLink`) REFERENCES `template` (`id`) ON UPDATE NO ACTION;

ALTER TABLE `rental`
  ADD CONSTRAINT `avatar_in_use_rental` FOREIGN KEY (`avatarLink`) REFERENCES `avatar` (`id`) ON UPDATE NO ACTION,
  ADD CONSTRAINT `notice_in_use_rental` FOREIGN KEY (`noticeLink`) REFERENCES `notice` (`id`) ON UPDATE NO ACTION,
  ADD CONSTRAINT `package_in_use_rental` FOREIGN KEY (`packageLink`) REFERENCES `package` (`id`) ON UPDATE NO ACTION,
  ADD CONSTRAINT `stream_in_use_rental` FOREIGN KEY (`streamLink`) REFERENCES `stream` (`id`) ON UPDATE NO ACTION;

ALTER TABLE `rentalnoticeptout`
  ADD CONSTRAINT `table: rental notice optout - Notice in use` FOREIGN KEY (`noticeLink`) REFERENCES `notice` (`id`) ON UPDATE NO ACTION,
  ADD CONSTRAINT `table: rental notice optout - Rental in use` FOREIGN KEY (`rentalLink`) REFERENCES `rental` (`id`) ON UPDATE NO ACTION;

ALTER TABLE `reseller`
  ADD CONSTRAINT `avatar_in_use_reseller` FOREIGN KEY (`avatarLink`) REFERENCES `avatar` (`id`) ON UPDATE NO ACTION;

ALTER TABLE `slconfig`
  ADD CONSTRAINT `avatar_in_use_config` FOREIGN KEY (`ownerAvatarLink`) REFERENCES `avatar` (`id`),
  ADD CONSTRAINT `timezone_in_use_config` FOREIGN KEY (`displayTimezoneLink`) REFERENCES `timezones` (`id`);

ALTER TABLE `staff`
  ADD CONSTRAINT `avatar_in_use_staff` FOREIGN KEY (`avatarLink`) REFERENCES `avatar` (`id`) ON UPDATE NO ACTION;

ALTER TABLE `stream`
  ADD CONSTRAINT `package_in_use_stream` FOREIGN KEY (`packageLink`) REFERENCES `package` (`id`) ON UPDATE NO ACTION,
  ADD CONSTRAINT `rental_in_use_stream` FOREIGN KEY (`rentalLink`) REFERENCES `rental` (`id`) ON UPDATE NO ACTION,
  ADD CONSTRAINT `server_in_use_stream` FOREIGN KEY (`serverLink`) REFERENCES `server` (`id`) ON UPDATE NO ACTION;

ALTER TABLE `transactions`
  ADD CONSTRAINT `avatar_in_use_transactions` FOREIGN KEY (`avatarLink`) REFERENCES `avatar` (`id`) ON UPDATE NO ACTION,
  ADD CONSTRAINT `package_in_use_transactions` FOREIGN KEY (`packageLink`) REFERENCES `package` (`id`) ON UPDATE NO ACTION,
  ADD CONSTRAINT `region_in_use_transactions` FOREIGN KEY (`regionLink`) REFERENCES `region` (`id`) ON UPDATE NO ACTION,
  ADD CONSTRAINT `reseller_in_use_transactions` FOREIGN KEY (`resellerLink`) REFERENCES `reseller` (`id`) ON UPDATE NO ACTION,
  ADD CONSTRAINT `stream_in_use_transactions` FOREIGN KEY (`streamLink`) REFERENCES `stream` (`id`) ON UPDATE NO ACTION;

ALTER TABLE `treevenderpackages`
  ADD CONSTRAINT `package_in_use_treevenderpackages` FOREIGN KEY (`packageLink`) REFERENCES `package` (`id`) ON UPDATE NO ACTION,
  ADD CONSTRAINT `treevender_in_use_treevenderpackages` FOREIGN KEY (`treevenderLink`) REFERENCES `treevender` (`id`) ON UPDATE NO ACTION;
ALTER TABLE `objects` ADD UNIQUE(`objectUUID`);

UPDATE `slconfig` SET `dbVersion` = '2.0.1.0' WHERE `slconfig`.`id` = 1;