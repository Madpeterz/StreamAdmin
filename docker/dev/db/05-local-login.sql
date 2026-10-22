-- Local test login: admin / localtest (owner level), linked to the "Madpeter Zond" avatar.
-- The password itself is set by DevLoginSeeder when the app container starts.
UPDATE `staff` SET
    `username` = 'admin',
    `avatarLink` = 2,
    `ownerLevel` = 1
WHERE `id` = 1;
