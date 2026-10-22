-- Work queues for the owner-only server endpoints. Requires catalog.sql.
SET FOREIGN_KEY_CHECKS = 0;

INSERT INTO `eventsq` (`id`, `eventName`, `eventMessage`, `eventUnixtime`) VALUES
(1, 'RentalStart', '{"rental":"rentActv"}', 1729026128),
(2, 'RentalExpire', '{"rental":"rentExpd"}', 1729026129);

INSERT INTO `message` (`id`, `avatarLink`, `message`) VALUES
(1, 3, 'Hello Contract Client, first queued message'),
(2, 4, 'Second queued message for Other Client');

INSERT INTO `noticenotecard` (`id`, `name`, `missing`) VALUES
(4, 'Contract missing card', 1);

INSERT INTO `notecardmail` (`id`, `avatarLink`, `noticenotecardLink`) VALUES
(1, 3, 2),
(2, 3, 1),
(3, 4, 4);

INSERT INTO `detail` (`id`, `rentalLink`) VALUES (1, 1);

INSERT INTO `botcommandq` (`id`, `command`, `args`, `unixtime`) VALUES
(1, 'IM', '["c11e0000-0000-4000-8000-000000000001","Bot queued hello"]', 1729026128),
(2, 'GroupInvite', '["9c000000-0000-4000-8000-000000000009","c11e0000-0000-4000-8000-000000000001","everyone"]', 1729026129);

-- Notice transitions: rental 4 drops from the 72h notice to the 24h notice (opted out of the 24h message),
-- rental 5 expired an hour ago while still on the 24h notice.
INSERT INTO `stream` (`id`, `serverLink`, `rentalLink`, `packageLink`, `port`, `needWork`, `adminUsername`, `adminPassword`, `djPassword`, `streamUid`, `mountpoint`) VALUES
(6, 1, 4, 1, 8006, 0, 'admin6', 'adminpass6', 'djpass6', 'strRent4', '/live'),
(7, 1, 5, 1, 8007, 0, 'admin7', 'adminpass7', 'djpass7', 'strRent5', '/live');

INSERT INTO `rental` (`id`, `avatarLink`, `streamLink`, `packageLink`, `noticeLink`, `startUnixtime`, `expireUnixtime`, `renewals`, `totalAmount`, `message`, `rentalUid`) VALUES
(4, 4, 6, 1, 3, UNIX_TIMESTAMP() - 518400, UNIX_TIMESTAMP() + 73800, 0, 500, NULL, 'rentDay1'),
(5, 3, 7, 1, 4, UNIX_TIMESTAMP() - 604800, UNIX_TIMESTAMP() - 3600, 0, 500, NULL, 'rentGone');

INSERT INTO `rentalnoticeptout` (`id`, `rentalLink`, `noticeLink`) VALUES (1, 4, 4);

SET FOREIGN_KEY_CHECKS = 1;
