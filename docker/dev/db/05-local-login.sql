-- Local test login: admin / localtest (owner level), linked to the "Madpeter Zond" avatar.
-- phash = first 42 chars of sha256(password . staff id . psalt . ownerLevel), see SessionControl::hashPassword.
UPDATE `staff` SET
    `username` = 'admin',
    `avatarLink` = 2,
    `ownerLevel` = 1,
    `psalt` = 'localdevsalt0001',
    `phash` = LEFT(SHA2(CONCAT('localtest', '1', 'localdevsalt0001', '1'), 256), 42),
    `lhash` = 'local'
WHERE `id` = 1;
