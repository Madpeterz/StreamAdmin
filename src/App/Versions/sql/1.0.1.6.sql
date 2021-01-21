UPDATE `slconfig` SET `db_version` = '1.0.1.7' WHERE `slconfig`.`id` = 1;
ALTER TABLE `apirequests` CHANGE `last_attempt` `lastAttempt` INT(11) NOT NULL DEFAULT '0';
ALTER TABLE `apis` 
CHANGE `opt_toggle_status` `optToggleStatus` TINYINT(1) NOT NULL DEFAULT '0', 
CHANGE `opt_password_reset` `optPasswordReset` TINYINT(1) NOT NULL DEFAULT '0', 
CHANGE `opt_autodj_next` `optAutodjNext` TINYINT(1) NOT NULL DEFAULT '0', 
CHANGE `opt_toggle_autodj` `optToggleAutodj` TINYINT(1) NOT NULL DEFAULT '0', 
CHANGE `event_enable_start` `eventEnableStart` TINYINT(1) NOT NULL DEFAULT '0', 
CHANGE `event_start_sync_username` `eventStartSyncUsername` TINYINT(1) NOT NULL DEFAULT '0', 
CHANGE `event_enable_renew` `eventEnableRenew` TINYINT(1) NOT NULL DEFAULT '0', 
CHANGE `event_disable_expire` `eventDisableExpire` TINYINT(1) NOT NULL DEFAULT '0', 
CHANGE `event_disable_revoke` `eventDisableRevoke` TINYINT(1) NOT NULL DEFAULT '0', 
CHANGE `event_revoke_reset_username` `eventRevokeResetUsername` TINYINT(1) NOT NULL DEFAULT '0', 
CHANGE `event_reset_password_revoke` `eventResetPasswordRevoke` TINYINT(1) NOT NULL DEFAULT '0', 
CHANGE `event_clear_djs` `eventClearDjs` TINYINT(1) NOT NULL DEFAULT '0', 
CHANGE `event_recreate_revoke` `eventRecreateRevoke` TINYINT(1) NOT NULL DEFAULT '0', 
CHANGE `event_create_stream` `eventCreateStream` TINYINT(1) NOT NULL DEFAULT '0', 
CHANGE `event_update_stream` `eventUpdateStream` TINYINT(1) NOT NULL DEFAULT '0';
ALTER TABLE `avatar` CHANGE `avataruuid` `avatarUUID` VARCHAR(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL;
ALTER TABLE `avatar` CHANGE `avatar_uid` `avatarUid` VARCHAR(8) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL;
ALTER TABLE `avatar` CHANGE `avatarname` `avatarName` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL;
ALTER TABLE `banlist` DROP INDEX `avatar_link`;
ALTER TABLE `banlist` CHANGE `avatar_link` `avatarLink` INT(11) NOT NULL;
ALTER TABLE `banlist` ADD INDEX(`avatarLink`);
ALTER TABLE `botconfig` DROP INDEX `avatarlink`;
ALTER TABLE `botconfig` CHANGE `avatarlink` `avatarLink` INT(11) NOT NULL;
ALTER TABLE `botconfig` ADD INDEX(`avatarLink`);
ALTER TABLE `detail` DROP INDEX `rentallink`;
ALTER TABLE `detail` CHANGE `rentallink` `rentalLink` INT(11) NOT NULL;
ALTER TABLE `detail` ADD INDEX(`rentalLink`);
ALTER TABLE `event` 
CHANGE `avatar_uuid` `avatarUUID` VARCHAR(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL, 
CHANGE `avatar_name` `avatarName` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL, 
CHANGE `rental_uid` `rentalUid` VARCHAR(8) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL, 
CHANGE `package_uid` `packageUid` VARCHAR(8) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL, 
CHANGE `event_new` `eventNew` TINYINT(1) NOT NULL DEFAULT '0', 
CHANGE `event_renew` `eventRenew` TINYINT(1) NOT NULL DEFAULT '0', 
CHANGE `event_expire` `eventExpire` TINYINT(1) NOT NULL DEFAULT '0', 
CHANGE `event_remove` `eventRemove` TINYINT(1) NOT NULL DEFAULT '0', 
CHANGE `expire_unixtime` `expireUnixtime` INT(11) NOT NULL;
ALTER TABLE `message` DROP INDEX `avatarlink`;
ALTER TABLE `message` CHANGE `avatarlink` `avatarLink` INT(11) NOT NULL;
ALTER TABLE `message` ADD INDEX(`avatarLink`);
ALTER TABLE `notecard` DROP INDEX `rentallink`;
ALTER TABLE `notecard` DROP INDEX `noticelink`;
ALTER TABLE `notecard` 
CHANGE `rentallink` `rentalLink` INT(11) NOT NULL, 
CHANGE `as_notice` `asNotice` TINYINT(1) NOT NULL DEFAULT '0', 
CHANGE `noticelink` `noticeLink` INT(11) NULL DEFAULT NULL;
ALTER TABLE `notecard` ADD INDEX(`noticeLink`);
ALTER TABLE `notecard` ADD INDEX(`rentalLink`);
ALTER TABLE `notice` DROP INDEX `notice_notecardlink`;
ALTER TABLE `notice` DROP INDEX `hoursremaining`;
ALTER TABLE `notice` 
CHANGE `immessage` `imMessage` VARCHAR(800) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL, 
CHANGE `usebot` `useBot` TINYINT(1) NOT NULL DEFAULT '0', 
CHANGE `send_notecard` `sendNotecard` TINYINT(1) NOT NULL DEFAULT '0', 
CHANGE `notecarddetail` `notecardDetail` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL, 
CHANGE `hoursremaining` `hoursRemaining` INT(11) NOT NULL DEFAULT '0', 
CHANGE `notice_notecardlink` `noticeNotecardLink` INT(11) NOT NULL DEFAULT '1';
ALTER TABLE `notice` ADD INDEX(`noticeNotecardLink`);
ALTER TABLE `objects` DROP INDEX `avatarlink`;
ALTER TABLE `objects` DROP INDEX `regionlink`;
ALTER TABLE `objects` DROP INDEX `objectmode`;
ALTER TABLE `objects` 
CHANGE `avatarlink` `avatarLink` INT(11) NOT NULL, 
CHANGE `regionlink` `regionLink` INT(11) NOT NULL, 
CHANGE `objectuuid` `objectUUID` VARCHAR(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL, 
CHANGE `objectname` `objectName` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL, 
CHANGE `objectmode` `objectMode` VARCHAR(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL, 
CHANGE `objectxyz` `objectXYZ` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL, 
CHANGE `lastseen` `lastSeen` INT(11) NOT NULL DEFAULT '0';
ALTER TABLE `objects` ADD INDEX(`avatarLink`);
ALTER TABLE `objects` ADD INDEX(`regionLink`);
ALTER TABLE `rental` DROP INDEX `streamlink`;
ALTER TABLE `rental` DROP INDEX `rental_uid`;
ALTER TABLE `rental` DROP INDEX `avatarlink`;
ALTER TABLE `rental` DROP INDEX `packagelink`;
ALTER TABLE `rental` DROP INDEX `noticelink`;
ALTER TABLE `rental` 
CHANGE `avatarlink` `avatarLink` INT(11) NOT NULL, 
CHANGE `streamlink` `streamLink` INT(11) NOT NULL, 
CHANGE `packagelink` `packageLink` INT(11) NOT NULL, 
CHANGE `noticelink` `noticeLink` INT(11) NOT NULL, 
CHANGE `startunixtime` `startUnixtime` INT(11) NOT NULL, 
CHANGE `expireunixtime` `expireUnixtime` INT(11) NOT NULL, 
CHANGE `totalamount` `totalAmount` INT(11) NOT NULL DEFAULT '0', 
CHANGE `rental_uid` `rentalUid` VARCHAR(8) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL;
ALTER TABLE `rental` ADD INDEX(`avatarLink`);
ALTER TABLE `rental` ADD UNIQUE(`streamLink`);
ALTER TABLE `rental` ADD INDEX(`packageLink`);
ALTER TABLE `rental` ADD INDEX(`noticeLink`);
ALTER TABLE `rental` ADD UNIQUE(`rentalUid`);
ALTER TABLE `reseller` DROP INDEX `avatarlink`;
ALTER TABLE `reseller` CHANGE `avatarlink` `avatarLink` INT(11) NOT NULL;
ALTER TABLE `reseller` ADD UNIQUE(`avatarLink`);
ALTER TABLE `server` DROP INDEX `apilink`;
ALTER TABLE `server` 
CHANGE `controlpanel_url` `controlPanelURL` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL, 
CHANGE `apilink` `apiLink` INT(11) NOT NULL DEFAULT '1', 
CHANGE `api_url` `apiURL` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, 
CHANGE `api_username` `apiUsername` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, 
CHANGE `api_password` `apiPassword` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, 
CHANGE `api_serverstatus` `apiServerStatus` TINYINT(1) NOT NULL DEFAULT '0', 
CHANGE `api_sync_accounts` `apiSyncAccounts` TINYINT(1) NOT NULL DEFAULT '0', 
CHANGE `opt_password_reset` `optPasswordReset` TINYINT(1) NOT NULL DEFAULT '0', 
CHANGE `opt_autodj_next` `optAutodjNext` TINYINT(1) NOT NULL DEFAULT '0', 
CHANGE `opt_toggle_autodj` `optToggleAutodj` TINYINT(1) NOT NULL DEFAULT '0', 
CHANGE `opt_toggle_status` `optToggleStatus` TINYINT(1) NOT NULL DEFAULT '0', 
CHANGE `event_enable_start` `eventEnableStart` TINYINT(1) NOT NULL DEFAULT '0', 
CHANGE `event_start_sync_username` `eventStartSyncUsername` TINYINT(1) NOT NULL DEFAULT '0', 
CHANGE `event_enable_renew` `eventEnableRenew` TINYINT(1) NOT NULL DEFAULT '0', 
CHANGE `event_disable_expire` `eventDisableExpire` TINYINT(1) NOT NULL DEFAULT '0', 
CHANGE `event_disable_revoke` `eventDisableRevoke` TINYINT(1) NOT NULL DEFAULT '0', 
CHANGE `event_revoke_reset_username` `eventRevokeResetUsername` TINYINT(1) NOT NULL DEFAULT '0', 
CHANGE `event_reset_password_revoke` `eventResetPasswordRevoke` TINYINT(1) NOT NULL DEFAULT '0', 
CHANGE `event_clear_djs` `eventClearDjs` TINYINT(1) NOT NULL DEFAULT '0', 
CHANGE `event_recreate_revoke` `eventRecreateRevoke` TINYINT(1) NOT NULL DEFAULT '0', 
CHANGE `last_api_sync` `lastApiSync` INT(11) NOT NULL DEFAULT '0', 
CHANGE `event_create_stream` `eventCreateStream` TINYINT(1) NOT NULL DEFAULT '0', 
CHANGE `event_update_stream` `eventUpdateStream` TINYINT(1) NOT NULL DEFAULT '0';
ALTER TABLE `server` ADD INDEX(`apiLink`);
ALTER TABLE `slconfig` DROP INDEX `owner_av`;
ALTER TABLE `slconfig` 
CHANGE `db_version` `dbVersion` VARCHAR(12) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'install', 
CHANGE `new_resellers` `newResellers` TINYINT(1) NOT NULL DEFAULT '0', 
CHANGE `new_resellers_rate` `newResellersRate` INT(3) NOT NULL DEFAULT '0', 
CHANGE `sllinkcode` `slLinkCode` VARCHAR(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL, 
CHANGE `clients_list_mode` `clientsListMode` TINYINT(1) NOT NULL DEFAULT '0', 
CHANGE `publiclinkcode` `publicLinkCode` VARCHAR(12) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, 
CHANGE `owner_av` `ownerAvatarLink` INT(11) NOT NULL, CHANGE `eventstorage` `eventStorage` TINYINT(1) NOT NULL DEFAULT '0', 
CHANGE `datatable_itemsperpage` `datatableItemsPerPage` INT(3) NOT NULL DEFAULT '10', 
CHANGE `http_inbound_secret` `httpInboundSecret` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL, 
CHANGE `smtp_host` `smtpHost` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, 
CHANGE `smtp_port` `smtpPort` INT(11) NULL DEFAULT NULL, 
CHANGE `smtp_username` `smtpUsername` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, 
CHANGE `smtp_accesscode` `smtpAccesscode` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, 
CHANGE `smtp_from` `smtpFrom` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, 
CHANGE `smtp_replyto` `smtpReplyTo` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, 
CHANGE `displaytimezonelink` `displayTimezoneLink` INT(11) NOT NULL DEFAULT '11', 
CHANGE `api_default_email` `apiDefaultEmail` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL;
ALTER TABLE `slconfig` ADD INDEX(`ownerAvatarLink`);
ALTER TABLE `slconfig` ADD INDEX(`displayTimezoneLink`);
ALTER TABLE `slconfig` ADD CONSTRAINT `slconfig_ibfk_2` FOREIGN KEY (`displayTimezoneLink`) REFERENCES `timezones`(`id`) ON DELETE RESTRICT ON UPDATE NO ACTION;
ALTER TABLE `staff` DROP INDEX `avatarlink`;
ALTER TABLE `staff` DROP INDEX `email_reset_code`;
ALTER TABLE `staff` 
CHANGE `email_reset_code` `emailResetCode` VARCHAR(8) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, 
CHANGE `email_reset_expires` `emailResetExpires` INT(11) NOT NULL DEFAULT '0', 
CHANGE `avatarlink` `avatarLink` INT(11) NOT NULL,
CHANGE `ownerlevel` `ownerLevel` TINYINT(1) NOT NULL DEFAULT '0';
ALTER TABLE `staff` ADD UNIQUE(`avatarLink`);
ALTER TABLE `staff` ADD UNIQUE(`emailResetCode`);
ALTER TABLE `stream` DROP INDEX `stream_uid`;
ALTER TABLE `stream` DROP INDEX `packagelink`;
ALTER TABLE `stream` DROP INDEX `rentallink`;
ALTER TABLE `stream` DROP INDEX `serverlink`;
ALTER TABLE `stream` 
CHANGE `serverlink` `serverLink` INT(11) NOT NULL, 
CHANGE `rentallink` `rentalLink` INT(11) NULL DEFAULT NULL, 
CHANGE `packagelink` `packageLink` INT(11) NOT NULL, 
CHANGE `needwork` `needWork` TINYINT(1) NOT NULL DEFAULT '0', 
CHANGE `original_adminusername` `originalAdminUsername` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL, 
CHANGE `adminusername` `adminUsername` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL, 
CHANGE `adminpassword` `adminPassword` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL, 
CHANGE `djpassword` `djPassword` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL, 
CHANGE `stream_uid` `streamUid` VARCHAR(8) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL, 
CHANGE `last_api_sync` `lastApiSync` INT(11) NOT NULL DEFAULT '0', 
CHANGE `api_uid_1` `apiConfigValue1` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, 
CHANGE `api_uid_2` `apiConfigValue2` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, 
CHANGE `api_uid_3` `apiConfigValue3` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL;
ALTER TABLE `stream` ADD INDEX(`serverLink`);
ALTER TABLE `stream` ADD UNIQUE(`rentalLink`);
ALTER TABLE `stream` ADD INDEX(`packageLink`);
ALTER TABLE `stream` ADD UNIQUE(`streamUid`);
ALTER TABLE `template` CHANGE `notecarddetail` `notecardDetail` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL;
ALTER TABLE `textureconfig` 
CHANGE `wait_owner` `waitOwner` VARCHAR(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL, 
CHANGE `stock_levels` `stockLevels` VARCHAR(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL, 
CHANGE `inuse` `inUse` VARCHAR(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL, 
CHANGE `renew_here` `renewHere` VARCHAR(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL, 
CHANGE `treevend_waiting` `treevendWaiting` VARCHAR(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL, 
CHANGE `proxyrenew` `proxyRenew` VARCHAR(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL, 
CHANGE `getting_details` `gettingDetails` VARCHAR(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL, 
CHANGE `request_details` `requestDetails` VARCHAR(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL;
ALTER TABLE `transactions` DROP INDEX `transaction_uid`;
ALTER TABLE `transactions` DROP INDEX `avatarlink`;
ALTER TABLE `transactions` DROP INDEX `packagelink`;
ALTER TABLE `transactions` DROP INDEX `streamlink`;
ALTER TABLE `transactions` DROP INDEX `resellerlink`;
ALTER TABLE `transactions` DROP INDEX `regionlink`;
ALTER TABLE `transactions` 
CHANGE `avatarlink` `avatarLink` INT(11) NOT NULL, 
CHANGE `packagelink` `packageLink` INT(11) NULL DEFAULT NULL, 
CHANGE `streamlink` `streamLink` INT(11) NULL DEFAULT NULL, 
CHANGE `resellerlink` `resellerLink` INT(11) NULL DEFAULT NULL, 
CHANGE `regionlink` `regionLink` INT(11) NULL DEFAULT NULL, 
CHANGE `transaction_uid` `transactionUid` VARCHAR(8) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL;
ALTER TABLE `transactions` ADD INDEX(`avatarLink`);
ALTER TABLE `transactions` ADD INDEX(`packageLink`);
ALTER TABLE `transactions` ADD INDEX(`streamLink`);
ALTER TABLE `transactions` ADD INDEX(`resellerLink`);
ALTER TABLE `transactions` ADD INDEX(`regionLink`);
ALTER TABLE `transactions` ADD UNIQUE(`transactionUid`);
ALTER TABLE `treevenderpackages` DROP INDEX `treevenderlink`;
ALTER TABLE `treevenderpackages` DROP INDEX `packagelink`;
ALTER TABLE `treevenderpackages` 
CHANGE `treevenderlink` `treevenderLink` INT(11) NOT NULL, 
CHANGE `packagelink` `packageLink` INT(11) NOT NULL;
ALTER TABLE `treevenderpackages` ADD INDEX(`treevenderLink`);
ALTER TABLE `treevenderpackages` ADD INDEX(`packageLink`);