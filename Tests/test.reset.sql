SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


TRUNCATE TABLE `auditlog`;
TRUNCATE TABLE `avatar`;
INSERT INTO `avatar` (`id`, `avatarUUID`, `avatarName`, `avatarUid`, `lastUsed`, `credits`) VALUES
(1, '00000000-0000-0000-0000-000000000000', 'System', 'System', 1729026128, 0),
(2, '289c3e36-69b3-40c5-9229-0c6a5d230766', 'Madpeter Zond', 'SysDevOp', 1729026128, 0);

TRUNCATE TABLE `banlist`;
TRUNCATE TABLE `botcommandq`;
TRUNCATE TABLE `botconfig`;
INSERT INTO `botconfig` (`id`, `avatarLink`, `secret`, `notecards`, `ims`, `invites`, `inviteGroupUUID`, `httpMode`, `httpURL`) VALUES
(1, 1, 'Signed command code', 0, 0, 0, NULL, 0, NULL);

TRUNCATE TABLE `datatable`;
INSERT INTO `datatable` (`id`, `hideColZero`, `col`, `cols`, `name`, `dir`) VALUES
(1, 1, 0, '0=id,1=region,3=Percentage,4=Count Up,5=Count down', 'Health', 'desc'),
(2, 1, 1, '0=id,1=Object,2=Last seen,5=Owner', 'Health / Detailed', 'desc'),
(3, 1, 0, '0=id,1=Rental UID,2=Avatar,3=Port,5=Timeleft,6=Status,7=Renewals', 'Client / List', 'desc'),
(4, 1, 0, '0=id,1=Stream UID,2=Server,3=Port', 'Stream / List', 'desc'),
(5, 1, 0, '0=id,1=Package name,2=Sold,3=Need work,4=Ready', 'Streams / Package menu', 'desc'),
(6, 1, 0, '0=id,1=Package UID,2=Name,4=Listeners,5=Days,6=Kbps,7=Cost', 'Packages / List', 'desc');

TRUNCATE TABLE `detail`;
TRUNCATE TABLE `eventsq`;
TRUNCATE TABLE `marketplacecoupons`;
TRUNCATE TABLE `message`;
TRUNCATE TABLE `notecard`;
TRUNCATE TABLE `notecardmail`;
TRUNCATE TABLE `notice`;
INSERT INTO `notice` (`id`, `name`, `imMessage`, `sendObjectIM`, `useBot`, `sendNotecard`, `notecardDetail`, `hoursRemaining`, `noticeNotecardLink`) VALUES
(1, '7 day notice', 'Hello [[AVATAR_FIRSTNAME]] your stream on [[SERVER_DOMAIN]] port [[STREAM_PORT]] now has [[RENTAL_TIMELEFT]] remaining', 1, 1, 0, '', 168, 1),
(2, '5 day notice', 'Hello [[AVATAR_FIRSTNAME]] your stream on [[SERVER_DOMAIN]] port [[STREAM_PORT]] now has [[RENTAL_TIMELEFT]]  remaining, When you have time please drop into our store.', 1, 1, 0, '', 120, 1),
(3, '3 day notice', 'Hello [[AVATAR_FIRSTNAME]] your stream on [[SERVER_DOMAIN]] port [[STREAM_PORT]] now has [[RENTAL_TIMELEFT]] remaining, Dont forget to renew your service!', 1, 1, 0, '', 72, 1),
(4, '1 day notice', 'Hello [[AVATAR_FIRSTNAME]] your stream on [[SERVER_DOMAIN]] port [[STREAM_PORT]] now has less than 24 hours remaining. Please renew to avoid loss of service.', 1, 1, 0, '', 24, 1),
(5, '5 hour notice', 'Hello [[AVATAR_FIRSTNAME]] your stream on [[SERVER_DOMAIN]] port [[STREAM_PORT]] now has less than 5 hours remaining. ', 1, 1, 0, '', 5, 1),
(6, 'Expired', 'Hello [[AVATAR_FIRSTNAME]] your stream on [[SERVER_DOMAIN]] port [[STREAM_PORT]]  has now expired please renew asap or risk losing the assigned port.', 1, 1, 0, '', 0, 1),
(10, 'Active', '', 1, 0, 0, '', 999, 1);

TRUNCATE TABLE `noticenotecard`;
INSERT INTO `noticenotecard` (`id`, `name`, `missing`) VALUES
(1, 'None', 0);

TRUNCATE TABLE `objects`;
TRUNCATE TABLE `package`;
TRUNCATE TABLE `region`;
TRUNCATE TABLE `rental`;
TRUNCATE TABLE `rentalnoticeptout`;
TRUNCATE TABLE `reseller`;
TRUNCATE TABLE `server`;
TRUNCATE TABLE `servertypes`;
INSERT INTO `servertypes` (`id`, `name`) VALUES
(3, 'Icecast'),
(1, 'ShoutcastV1'),
(2, 'ShoutcastV2');

TRUNCATE TABLE `slconfig`;
INSERT INTO `slconfig` (`id`, `dbVersion`, `newResellers`, `newResellersRate`, `slLinkCode`, `clientsListMode`, `publicLinkCode`, `hudLinkCode`, `ownerAvatarLink`, `datatableItemsPerPage`, `httpInboundSecret`, `displayTimezoneLink`, `hudAllowDiscord`, `hudDiscordLink`, `hudAllowGroup`, `hudGroupLink`, `hudAllowDetails`, `hudAllowRenewal`, `eventsAPI`, `paymentKey`, `streamListOption`, `clientsDisplayServer`, `limitStreams`, `limitTime`, `maxStreamTimeDays`, `maxTotalStreams`, `ansSalt`, `enableCoupons`) VALUES
(1, '2.0.1.1', 0, 0, 'install', 1, 'install', 'install', 1, 10, 'install', 11, 0, 'Not setup yet', 0, 'Not setup yet', 0, 0, 0, 'install', 1, 1, 0, 0, 120, 100, 'Not used', 0);

TRUNCATE TABLE `staff`;
INSERT INTO `staff` (`id`, `username`, `emailResetCode`, `emailResetExpires`, `avatarLink`, `phash`, `lhash`, `psalt`, `ownerLevel`) VALUES
(1, 'Install', NULL, 0, 1, 'Install', 'Install', 'Install', 1);

TRUNCATE TABLE `stream`;
TRUNCATE TABLE `template`;
INSERT INTO `template` (`id`, `name`, `detail`, `notecardDetail`) VALUES
(1, 'Shoutcast', 'Package: [[PACKAGE_NAME]][[NL]]\r\nListeners: [[PACKAGE_LISTENERS]][[NL]]\r\nBitrate: [[PACKAGE_BITRATE]]kbps[[NL]]\r\nAutoDJ: [[PACKAGE_AUTODJ]] [[PACKAGE_AUTODJ_SIZE]]gb[[NL]]\r\n[[NL]]\r\nControl panel: [[SERVER_CONTROLPANEL]][[NL]]\r\nDomain: [[SERVER_DOMAIN]][[NL]]\r\nport: [[STREAM_PORT]][[NL]]\r\n[[NL]]\r\nAdmin user: [[STREAM_ADMINUSERNAME]][[NL]]\r\nAdmin pass: [[STREAM_ADMINPASSWORD]][[NL]]\r\nDJ pass: [[STREAM_DJPASSWORD]][[NL]]\r\n[[NL]]\r\nExpires: [[RENTAL_EXPIRES_DATETIME]]', 'Package: [[PACKAGE_NAME]][[NL]] \r\nListeners: [[PACKAGE_LISTENERS]][[NL]] \r\nBitrate: [[PACKAGE_BITRATE]]kbps[[NL]] \r\nAutoDJ: [[PACKAGE_AUTODJ]] [[PACKAGE_AUTODJ_SIZE]]gb[[NL]] \r\n[[NL]] \r\nControl panel: [[SERVER_CONTROLPANEL]][[NL]] \r\nDomain: [[SERVER_DOMAIN]][[NL]] \r\nport: [[STREAM_PORT]][[NL]] [[NL]] \r\nAdmin user: [[STREAM_ADMINUSERNAME]][[NL]] \r\nAdmin pass: [[STREAM_ADMINPASSWORD]][[NL]] \r\nDJ pass: [[STREAM_DJPASSWORD]][[NL]] \r\n[[NL]] \r\nExpires: [[RENTAL_EXPIRES_DATETIME]]'),
(2, 'Icecast', 'Package: [[PACKAGE_NAME]][[NL]]\r\nListeners: [[PACKAGE_LISTENERS]][[NL]]\r\nBitrate: [[PACKAGE_BITRATE]]kbps[[NL]]\r\nAutoDJ: [[PACKAGE_AUTODJ]] [[PACKAGE_AUTODJ_SIZE]]gb[[NL]]\r\n[[NL]]\r\nControl panel: [[SERVER_CONTROLPANEL]][[NL]]\r\nDomain: [[SERVER_DOMAIN]][[NL]]\r\nport: [[STREAM_PORT]][[NL]]\r\n[[NL]]\r\nAdmin user: [[STREAM_ADMINUSERNAME]][[NL]]\r\nAdmin pass: [[STREAM_ADMINPASSWORD]][[NL]]\r\nDJ pass: [[STREAM_DJPASSWORD]][[NL]]\r\nMountpoint: [[STREAM_MOUNTPOINT]][[NL]]\r\n[[NL]]\r\nExpires: [[RENTAL_EXPIRES_DATETIME]]', 'Package: [[PACKAGE_NAME]][[NL]] \r\nListeners: [[PACKAGE_LISTENERS]][[NL]] \r\nBitrate: [[PACKAGE_BITRATE]]kbps[[NL]] \r\nAutoDJ: [[PACKAGE_AUTODJ]] [[PACKAGE_AUTODJ_SIZE]]gb[[NL]]\r\n[[NL]] \r\nControl panel: [[SERVER_CONTROLPANEL]][[NL]] \r\nDomain: [[SERVER_DOMAIN]][[NL]] \r\nport: [[STREAM_PORT]][[NL]] \r\n[[NL]] \r\nAdmin user: [[STREAM_ADMINUSERNAME]][[NL]] \r\nAdmin pass: [[STREAM_ADMINPASSWORD]][[NL]] \r\nDJ pass: [[STREAM_DJPASSWORD]][[NL]] \r\nMountpoint: [[STREAM_MOUNTPOINT]][[NL]] \r\n[[NL]] \r\nExpires: [[RENTAL_EXPIRES_DATETIME]]');

TRUNCATE TABLE `textureconfig`;
INSERT INTO `textureconfig` (`id`, `name`, `offline`, `waitOwner`, `stockLevels`, `makePayment`, `inUse`, `renewHere`, `proxyRenew`, `gettingDetails`, `requestDetails`) VALUES
(1, 'SA7 defaults', '718fdaf8-df99-5c7f-48fb-feb94db12675', '51d5f381-43cd-84f0-c226-f9f89c12af7e', '257c594e-41d8-53d8-5280-5329a259a5d8', '19e57cf0-254f-32d7-fc9f-0d698aca4dc2', '10b68027-7e7f-fbbc-0c9f-6afabbfc636c', '0e99005c-526e-468c-7c0c-2569096f6162', 'cc1c1124-b5d0-595b-12b6-016c61b82456', 'bc14cd11-edca-4bd2-3a21-46d870966edd', 'c724a9ea-ee79-6d80-3249-ff016de063b0');

TRUNCATE TABLE `timezones`;
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

TRUNCATE TABLE `transactions`;
TRUNCATE TABLE `treevender`;
TRUNCATE TABLE `treevenderpackages`;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
SET FOREIGN_KEY_CHECKS = 1;
