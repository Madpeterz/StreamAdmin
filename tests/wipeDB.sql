CREATE DATABASE IF NOT EXISTS `test` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

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
`rollbacktest`, `twintables1`, `twintables2`, `weirdtable`, `eventsq`, `datatable`, `notecardmail`, `botcommandq`, `rentalnoticeptout`;
SET FOREIGN_KEY_CHECKS = 1;