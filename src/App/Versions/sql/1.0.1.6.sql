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
ALTER TABLE `banlist` DROP FOREIGN KEY avatar_in_use_banlist;
ALTER TABLE `banlist` DROP INDEX `avatar_link`;
ALTER TABLE `banlist` CHANGE `avatar_link` `avatarLink` INT(11) NOT NULL;
ALTER TABLE `banlist` ADD INDEX(`avatarLink`);
ALTER TABLE `banlist` ADD CONSTRAINT `avatar_in_use_banlist` FOREIGN KEY (`avatarLink`) REFERENCES `avatar`(`id`) ON DELETE RESTRICT ON UPDATE NO ACTION;
ALTER TABLE `botconfig` DROP FOREIGN KEY botconfig_ibfk_1;
ALTER TABLE `botconfig` DROP INDEX `avatarlink`;
ALTER TABLE `botconfig` CHANGE `avatarlink` `avatarLink` INT(11) NOT NULL;
ALTER TABLE `botconfig` ADD INDEX(`avatarLink`);
ALTER TABLE `botconfig` ADD CONSTRAINT `avatar_in_use_botconfig` FOREIGN KEY (`avatarLink`) REFERENCES `avatar`(`id`) ON DELETE RESTRICT ON UPDATE NO ACTION;
ALTER TABLE `detail` DROP FOREIGN KEY detail_ibfk_1;
ALTER TABLE `detail` DROP INDEX `rentallink`;
ALTER TABLE `detail` CHANGE `rentallink` `rentalLink` INT(11) NOT NULL;
ALTER TABLE `detail` ADD INDEX(`rentalLink`);
ALTER TABLE `detail` ADD CONSTRAINT `rental_in_use_detail` FOREIGN KEY (`rentalLink`) REFERENCES `rental`(`id`) ON DELETE RESTRICT ON UPDATE NO ACTION;
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
ALTER TABLE `message` DROP FOREIGN KEY message_ibfk_1;
ALTER TABLE `message` DROP INDEX `avatarlink`;
ALTER TABLE `message` CHANGE `avatarlink` `avatarLink` INT(11) NOT NULL;
ALTER TABLE `message` ADD INDEX(`avatarLink`);
ALTER TABLE `message` ADD CONSTRAINT `avatar_in_use_message` FOREIGN KEY (`avatarLink`) REFERENCES `avatar`(`id`) ON DELETE RESTRICT ON UPDATE NO ACTION;
ALTER TABLE `notecard` DROP FOREIGN KEY notecard_ibfk_1;
ALTER TABLE `notecard` DROP FOREIGN KEY notecard_ibfk_2;
ALTER TABLE `notecard` DROP INDEX `rentallink`;
ALTER TABLE `notecard` DROP INDEX `noticelink`;
ALTER TABLE `notecard` 
CHANGE `rentallink` `rentalLink` INT(11) NOT NULL, 
CHANGE `as_notice` `asNotice` TINYINT(1) NOT NULL DEFAULT '0', 
CHANGE `noticelink` `noticeLink` INT(11) NULL DEFAULT NULL;
ALTER TABLE `notecard` ADD INDEX(`noticeLink`);
ALTER TABLE `notecard` ADD INDEX(`rentalLink`);
ALTER TABLE `notecard` ADD CONSTRAINT `rental_in_use_notecard` FOREIGN KEY (`rentalLink`) REFERENCES `rental`(`id`) ON DELETE RESTRICT ON UPDATE NO ACTION; 
ALTER TABLE `notecard` ADD CONSTRAINT `notice_in_use_notecard` FOREIGN KEY (`noticeLink`) REFERENCES `notice`(`id`) ON DELETE RESTRICT ON UPDATE NO ACTION;
ALTER TABLE `notice` DROP FOREIGN KEY notice_notice_notecard_inuse;
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
ALTER TABLE `notice` ADD CONSTRAINT `noticenotcard_in_use_notice` FOREIGN KEY (`noticeNotecardLink`) REFERENCES `noticenotecard`(`id`) ON DELETE RESTRICT ON UPDATE NO ACTION;
ALTER TABLE `objects` DROP FOREIGN KEY objects_ibfk_1;
ALTER TABLE `objects` DROP FOREIGN KEY objects_ibfk_2;
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
ALTER TABLE `objects` ADD CONSTRAINT `avatar_in_use_objects` FOREIGN KEY (`avatarLink`) REFERENCES `avatar`(`id`) ON DELETE RESTRICT ON UPDATE NO ACTION; 
ALTER TABLE `objects` ADD CONSTRAINT `region_in_use_objects` FOREIGN KEY (`regionLink`) REFERENCES `region`(`id`) ON DELETE RESTRICT ON UPDATE NO ACTION;
ALTER TABLE `rental` DROP FOREIGN KEY rental_ibfk_1;
ALTER TABLE `rental` DROP FOREIGN KEY rental_ibfk_2;
ALTER TABLE `rental` DROP FOREIGN KEY rental_ibfk_3;
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
ALTER TABLE `rental` ADD CONSTRAINT `stream_in_use_rental` FOREIGN KEY (`streamLink`) REFERENCES `stream`(`id`) ON DELETE RESTRICT ON UPDATE RESTRICT; 
ALTER TABLE `rental` ADD CONSTRAINT `avatar_in_use_rental` FOREIGN KEY (`avatarLink`) REFERENCES `avatar`(`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;
ALTER TABLE `rental` ADD CONSTRAINT `package_in_use_rental` FOREIGN KEY (`packageLink`) REFERENCES `package`(`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;
ALTER TABLE `rental` ADD CONSTRAINT `notice_in_use_rental` FOREIGN KEY (`noticeLink`) REFERENCES `notice`(`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;
ALTER TABLE `reseller` DROP FOREIGN KEY reseller_ibfk_1;
ALTER TABLE `reseller` DROP INDEX `avatarlink`;
ALTER TABLE `reseller` CHANGE `avatarlink` `avatarLink` INT(11) NOT NULL;
ALTER TABLE `reseller` ADD UNIQUE(`avatarLink`);
ALTER TABLE `reseller` ADD CONSTRAINT `avatar_in_use_reseller` FOREIGN KEY (`avatarLink`) REFERENCES `avatar`(`id`) ON DELETE RESTRICT ON UPDATE NO ACTION;
ALTER TABLE `server` DROP FOREIGN KEY server_api_inuse;
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
ALTER TABLE `server` ADD CONSTRAINT `api_in_use_server` FOREIGN KEY (`apiLink`) REFERENCES `apis`(`id`) ON DELETE RESTRICT ON UPDATE NO ACTION;
ALTER TABLE `slconfig` DROP FOREIGN KEY slconfig_ibfk_1;
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
ALTER TABLE `slconfig` ADD CONSTRAINT `avatar_in_use_config` FOREIGN KEY (`ownerAvatarLink`) REFERENCES `avatar`(`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;
ALTER TABLE `slconfig` ADD CONSTRAINT `timezone_in_use_config` FOREIGN KEY (`displayTimezoneLink`) REFERENCES `timezones`(`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;
ALTER TABLE `staff` DROP FOREIGN KEY staff_ibfk_1;
ALTER TABLE `staff` DROP INDEX `avatarlink`;
ALTER TABLE `staff` DROP INDEX `email_reset_code`;
ALTER TABLE `staff` 
CHANGE `email_reset_code` `emailResetCode` VARCHAR(8) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, 
CHANGE `email_reset_expires` `emailResetExpires` INT(11) NOT NULL DEFAULT '0', 
CHANGE `avatarlink` `avatarLink` INT(11) NOT NULL,
CHANGE `ownerlevel` `ownerLevel` TINYINT(1) NOT NULL DEFAULT '0';
ALTER TABLE `staff` ADD UNIQUE(`avatarLink`);
ALTER TABLE `staff` ADD UNIQUE(`emailResetCode`);
ALTER TABLE `staff` ADD CONSTRAINT `avatar_in_use_staff` FOREIGN KEY (`avatarLink`) REFERENCES `avatar`(`id`) ON DELETE RESTRICT ON UPDATE NO ACTION;
ALTER TABLE `stream` DROP FOREIGN KEY stream_ibfk_1;
ALTER TABLE `stream` DROP FOREIGN KEY stream_ibfk_2;
ALTER TABLE `stream` DROP FOREIGN KEY stream_ibfk_3;
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
ALTER TABLE `stream` ADD CONSTRAINT `package_in_use_stream` FOREIGN KEY (`packageLink`) REFERENCES `package`(`id`) ON DELETE RESTRICT ON UPDATE NO ACTION;
ALTER TABLE `stream` ADD CONSTRAINT `rental_in_use_stream` FOREIGN KEY (`rentalLink`) REFERENCES `rental`(`id`) ON DELETE RESTRICT ON UPDATE NO ACTION;
ALTER TABLE `stream` ADD CONSTRAINT `server_in_use_stream` FOREIGN KEY (`serverLink`) REFERENCES `server`(`id`) ON DELETE RESTRICT ON UPDATE NO ACTION;
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
ALTER TABLE `transactions` DROP FOREIGN KEY transactions_ibfk_1;
ALTER TABLE `transactions` DROP FOREIGN KEY transactions_ibfk_2;
ALTER TABLE `transactions` DROP FOREIGN KEY transactions_ibfk_3;
ALTER TABLE `transactions` DROP FOREIGN KEY transactions_ibfk_4;
ALTER TABLE `transactions` DROP FOREIGN KEY transactions_ibfk_5;
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
ALTER TABLE `transactions` ADD CONSTRAINT `avatar_in_use_transactions` FOREIGN KEY (`avatarLink`) REFERENCES `avatar`(`id`) ON DELETE RESTRICT ON UPDATE NO ACTION; 
ALTER TABLE `transactions` ADD CONSTRAINT `package_in_use_transactions` FOREIGN KEY (`packageLink`) REFERENCES `package`(`id`) ON DELETE RESTRICT ON UPDATE NO ACTION; 
ALTER TABLE `transactions` ADD CONSTRAINT `region_in_use_transactions` FOREIGN KEY (`regionLink`) REFERENCES `region`(`id`) ON DELETE RESTRICT ON UPDATE NO ACTION; 
ALTER TABLE `transactions` ADD CONSTRAINT `reseller_in_use_transactions` FOREIGN KEY (`resellerLink`) REFERENCES `reseller`(`id`) ON DELETE RESTRICT ON UPDATE NO ACTION; 
ALTER TABLE `transactions` ADD CONSTRAINT `stream_in_use_transactions` FOREIGN KEY (`streamLink`) REFERENCES `stream`(`id`) ON DELETE RESTRICT ON UPDATE NO ACTION;
ALTER TABLE `treevenderpackages` DROP FOREIGN KEY treevenderpackages_ibfk_1;
ALTER TABLE `treevenderpackages` DROP FOREIGN KEY treevenderpackages_ibfk_2;
ALTER TABLE `treevenderpackages` DROP INDEX `treevenderlink`;
ALTER TABLE `treevenderpackages` DROP INDEX `packagelink`;
ALTER TABLE `treevenderpackages` 
CHANGE `treevenderlink` `treevenderLink` INT(11) NOT NULL, 
CHANGE `packagelink` `packageLink` INT(11) NOT NULL;
ALTER TABLE `treevenderpackages` ADD INDEX(`treevenderLink`);
ALTER TABLE `treevenderpackages` ADD INDEX(`packageLink`);
ALTER TABLE `treevenderpackages` ADD CONSTRAINT `package_in_use_treevenderpackages` FOREIGN KEY (`packageLink`) REFERENCES `package`(`id`) ON DELETE RESTRICT ON UPDATE NO ACTION;
ALTER TABLE `treevenderpackages` ADD CONSTRAINT `treevender_in_use_treevenderpackages` FOREIGN KEY (`treevenderLink`) REFERENCES `treevender`(`id`) ON DELETE RESTRICT ON UPDATE NO ACTION;
ALTER TABLE `apis` 
CHANGE `api_serverstatus` `apiServerStatus` TINYINT(1) NOT NULL DEFAULT '0', 
CHANGE `api_sync_accounts` `apiSyncAccounts` TINYINT(1) NOT NULL DEFAULT '0';
ALTER TABLE `package` DROP FOREIGN KEY package_ibfk_1;
ALTER TABLE `package` DROP FOREIGN KEY package_ibfk_2;
ALTER TABLE `package` DROP INDEX `servertypelink`;
ALTER TABLE `package` DROP INDEX `templatelink`;
ALTER TABLE `package` DROP INDEX `package_uid`;
ALTER TABLE `package` 
CHANGE `package_uid` `packageUid` VARCHAR(8) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL, 
CHANGE `autodj_size` `autodjSize` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, 
CHANGE `templatelink` `templateLink` INT(11) NULL DEFAULT NULL, 
CHANGE `servertypelink` `servertypeLink` INT(11) NOT NULL DEFAULT '1', 
CHANGE `texture_uuid_soldout` `textureSoldout` VARCHAR(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL, 
CHANGE `texture_uuid_instock_small` `textureInstockSmall` VARCHAR(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL, 
CHANGE `texture_uuid_instock_selected` `textureInstockSelected` VARCHAR(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL, 
CHANGE `api_template` `apiTemplate` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL;
ALTER TABLE `package` ADD INDEX(`servertypeLink`);
ALTER TABLE `package` ADD INDEX(`templateLink`);
ALTER TABLE `package` ADD UNIQUE(`packageUid`);
ALTER TABLE `package` ADD CONSTRAINT `servertype_in_use_package` FOREIGN KEY (`servertypeLink`) REFERENCES `servertypes`(`id`) ON DELETE RESTRICT ON UPDATE NO ACTION;
ALTER TABLE `package` ADD CONSTRAINT `template_in_use_package` FOREIGN KEY (`templateLink`) REFERENCES `template`(`id`) ON DELETE RESTRICT ON UPDATE NO ACTION;
ALTER TABLE `textureconfig` CHANGE `make_payment` `makePayment` VARCHAR(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL;
UPDATE `apis` SET `name` = 'None' WHERE `apis`.`id` = 1; 
UPDATE `apis` SET `name` = 'Centova3' WHERE `apis`.`id` = 2; 
UPDATE `apis` SET `name` = 'MediaCp' WHERE `apis`.`id` = 3; 
UPDATE `apis` SET `name` = 'WhmSonic' WHERE `apis`.`id` = 4; 
UPDATE `apis` SET `name` = 'Secondbot' WHERE `apis`.`id` = 5; 
UPDATE `apis` SET `name` = 'Azurecast' WHERE `apis`.`id` = 6;