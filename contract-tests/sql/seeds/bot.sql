-- Bot with IMs, notecards and group invites enabled. Requires catalog.sql (and usually queues.sql).
INSERT INTO `avatar` (`id`, `avatarUUID`, `avatarName`, `avatarUid`, `lastUsed`, `credits`) VALUES
(7, 'b0700000-0000-4000-8000-000000000007', 'Contract Bot', 'CtBot001', 1729026128, 0);

UPDATE `botconfig` SET `avatarLink` = 7, `notecards` = 1, `ims` = 1, `invites` = 1,
    `inviteGroupUUID` = '9c000000-0000-4000-8000-000000000009', `httpMode` = 0, `httpURL` = NULL WHERE `id` = 1;

UPDATE `notice` SET `sendNotecard` = 1,
    `notecardDetail` = 'Expired notice for [[AVATAR_FULLNAME]] port [[STREAM_PORT]] on [[SERVER_DOMAIN]]' WHERE `id` = 6;

UPDATE `package` SET `enableGroupInvite` = 1 WHERE `id` = 1;
