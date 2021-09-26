CREATE DATABASE IF NOT EXISTS `r4test` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
CREATE DATABASE IF NOT EXISTS `test` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

USE r4test;
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS  `items`, `packages`, `sales_tracking`, `users`;
SET FOREIGN_KEY_CHECKS = 1;
USE test;
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS  `apirequests`, `apis`, `avatar`, `banlist`,
`botconfig`, `detail`, `event`, `message`, `notecard`,
`notice`, `noticenotecard`, `objects`, `package`,
`region`, `rental`, `reseller`, `server`,
`servertypes`, `slconfig`, `staff`, `stream`,
`template`, `textureconfig`, `timezones`,
`transactions`, `treevender`, `treevenderpackages`,
`alltypestable`, `counttoonehundo`, `endoftestempty`, 
`endoftestwithfourentrys`, `endoftestwithupdates`, `flagedvalues`, 
`liketests`, `relationtestinga`, `relationtestingb`, 
`rollbacktest`, `twintables1`, `twintables2`, `weirdtable`, `eventsq`, `datatable`;
SET FOREIGN_KEY_CHECKS = 1;