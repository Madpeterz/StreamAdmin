-- Demo catalog for the dev stack (legacy table names; the migrations upgrade it).
-- Every package has at most one free stream so purchases are deterministic (stream choice is random).
-- Times are offset by 30 minutes so day/hour text ("7 days, 0 hours") is stable during a run.

SET FOREIGN_KEY_CHECKS = 0;

INSERT INTO `avatar` (`id`, `avatarUUID`, `avatarName`, `avatarUid`, `lastUsed`, `credits`) VALUES
(3, 'c11e0000-0000-4000-8000-000000000001', 'Contract Client', 'CtClient', 1729026128, 0),
(4, 'c11e0000-0000-4000-8000-000000000002', 'Other Client', 'CtOther1', 1729026128, 0),
(5, 'ba11ed00-0000-4000-8000-000000000005', 'Banned Resident', 'CtBanned', 1729026128, 0),
(6, 'c4ed1700-0000-4000-8000-000000000006', 'Credit Holder', 'CtCredit', 1729026128, 250);

INSERT INTO `banlist` (`id`, `avatarLink`) VALUES (1, 5);

INSERT INTO `reseller` (`id`, `avatarLink`, `allowed`, `rate`) VALUES
(1, 1, 1, 100),
(2, 2, 1, 25);

INSERT INTO `server` (`id`, `domain`, `controlPanelURL`, `ipaddress`, `bandwidth`, `bandwidthType`, `totalStorage`, `totalStorageType`) VALUES
(1, 'stream.contract.test', 'https://cp.contract.test', '10.0.0.1', 1000, 'mbps', 100, 'gb');

INSERT INTO `noticenotecard` (`id`, `name`, `missing`) VALUES
(2, 'Contract welcome', 0),
(3, 'Contract setup', 0);

INSERT INTO `package` (`id`, `packageUid`, `name`, `autodj`, `autodjSize`, `listeners`, `bitrate`, `templateLink`, `servertypeLink`, `cost`, `days`, `textureSoldout`, `textureInstockSmall`, `textureInstockSelected`, `welcomeNotecardLink`, `setupNotecardLink`, `enableGroupInvite`, `enforceCustomMaxStreams`, `maxStreamsInPackage`) VALUES
(1, 'pkgShout', 'Contract Shoutcast', 0, '0', 50, 128, 1, 1, 500, 7, 'a0000000-0000-4000-8000-00000000000a', 'b0000000-0000-4000-8000-00000000000b', 'c0000000-0000-4000-8000-00000000000c', 2, 3, 0, 0, 1),
(2, 'pkgIce01', 'Contract Icecast', 1, '5', 100, 192, 2, 3, 900, 30, 'a0000000-0000-4000-8000-00000000001a', 'b0000000-0000-4000-8000-00000000001b', 'c0000000-0000-4000-8000-00000000001c', 1, 1, 0, 0, 1),
(3, 'pkgEmpty', 'Contract Sold Out', 0, '0', 25, 64, 1, 2, 250, 7, 'a0000000-0000-4000-8000-00000000002a', 'b0000000-0000-4000-8000-00000000002b', 'c0000000-0000-4000-8000-00000000002c', 1, 1, 0, 0, 1);

-- stream 1: free (pkgShout), stream 2: rented by rental 1 (pkgShout), stream 3: rented by rental 2 (pkgIce01),
-- stream 4: free but needs work (pkgIce01), stream 5: rented by rental 3 (pkgEmpty)
INSERT INTO `stream` (`id`, `serverLink`, `rentalLink`, `packageLink`, `port`, `needWork`, `adminUsername`, `adminPassword`, `djPassword`, `streamUid`, `mountpoint`) VALUES
(1, 1, NULL, 1, 8001, 0, 'admin1', 'adminpass1', 'djpass1', 'strFree1', '/live'),
(2, 1, 1, 1, 8002, 0, 'admin2', 'adminpass2', 'djpass2', 'strRent1', '/live'),
(3, 1, 2, 2, 8003, 0, 'admin3', 'adminpass3', 'djpass3', 'strRent2', '/stream'),
(4, 1, NULL, 2, 8004, 1, 'admin4', 'adminpass4', 'djpass4', 'strWork1', '/stream'),
(5, 1, 3, 3, 8005, 0, 'admin5', 'adminpass5', 'djpass5', 'strRent3', '/live');

-- rental 1: active (client), rental 2: expired yesterday (other client), rental 3: 2 days left (client)
INSERT INTO `rental` (`id`, `avatarLink`, `streamLink`, `packageLink`, `noticeLink`, `startUnixtime`, `expireUnixtime`, `renewals`, `totalAmount`, `message`, `rentalUid`) VALUES
(1, 3, 2, 1, 10, UNIX_TIMESTAMP() - 604800, UNIX_TIMESTAMP() + 606600, 1, 1000, 'Contract note', 'rentActv'),
(2, 4, 3, 2, 6, UNIX_TIMESTAMP() - 2678400, UNIX_TIMESTAMP() - 84600, 0, 900, NULL, 'rentExpd'),
(3, 3, 5, 3, 3, UNIX_TIMESTAMP() - 432000, UNIX_TIMESTAMP() + 174600, 0, 250, NULL, 'rentSoon');

INSERT INTO `treevender` (`id`, `name`, `textureWaiting`, `textureInuse`, `hideSoldout`) VALUES
(1, 'Contract tree', 'd0000000-0000-4000-8000-00000000000d', 'e0000000-0000-4000-8000-00000000000e', 0),
(2, 'Empty tree', 'd0000000-0000-4000-8000-00000000001d', 'e0000000-0000-4000-8000-00000000001e', 1);

INSERT INTO `treevenderpackages` (`id`, `treevenderLink`, `packageLink`) VALUES
(1, 1, 1),
(2, 1, 2),
(3, 1, 3);

SET FOREIGN_KEY_CHECKS = 1;
