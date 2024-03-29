SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
CREATE DATABASE IF NOT EXISTS `test` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `test`;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS  `apirequests`, `apis`, `avatar`, `banlist`,
`botconfig`, `detail`, `event`, `message`, `notecard`,
`notice`, `noticenotecard`, `objects`, `package`,
`region`, `rental`, `reseller`, `server`,
`servertypes`, `slconfig`, `staff`, `stream`,
`template`, `textureconfig`, `timezones`,
`transactions`, `treevender`, `treevenderpackages`,
`alltypestable`, `counttoonehundo`, `endoftestempty`, 
`endoftestwithfourentrys`, `endoftestwithupdates`, `flagedvalues`, `liketests`, `relationtestinga`, 
`relationtestingb`, `rollbacktest`, `twintables1`,
`twintables2`, `weirdtable`, `notecardmail`, `eventsq`, `datatable`, `botcommandq`, `rentalnoticeptout`;



SET FOREIGN_KEY_CHECKS = 1;
CREATE TABLE `alltypestable` (
  `id` int(11) NOT NULL,
  `stringfield` mediumtext NOT NULL,
  `intfield` int(11) NOT NULL,
  `floatfield` double NOT NULL,
  `boolfield` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `counttoonehundo` (
  `id` int(11) NOT NULL,
  `cvalue` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `counttoonehundo` (`id`, `cvalue`) VALUES
(1, 1),
(2, 2),
(3, 4),
(4, 8),
(5, 16),
(6, 32),
(7, 64),
(8, 128),
(9, 256),
(10, 512),
(11, 1),
(12, 2),
(13, 4),
(14, 8),
(15, 16),
(16, 32),
(17, 64),
(18, 128),
(19, 256),
(20, 512),
(21, 1),
(22, 2),
(23, 4),
(24, 8),
(25, 16),
(26, 32),
(27, 64),
(28, 128),
(29, 256),
(30, 512),
(31, 1),
(32, 2),
(33, 4),
(34, 8),
(35, 16),
(36, 32),
(37, 64),
(38, 128),
(39, 256),
(40, 512),
(41, 1),
(42, 2),
(43, 4),
(44, 8),
(45, 16),
(46, 32),
(47, 64),
(48, 128),
(49, 256),
(50, 512),
(51, 1),
(52, 2),
(53, 4),
(54, 8),
(55, 16),
(56, 32),
(57, 64),
(58, 128),
(59, 256),
(60, 512),
(61, 1),
(62, 2),
(63, 4),
(64, 8),
(65, 16),
(66, 32),
(67, 64),
(68, 128),
(69, 256),
(70, 512),
(71, 1),
(72, 2),
(73, 4),
(74, 8),
(75, 16),
(76, 32),
(77, 64),
(78, 128),
(79, 256),
(80, 512),
(81, 1),
(82, 2),
(83, 4),
(84, 8),
(85, 16),
(86, 32),
(87, 64),
(88, 128),
(89, 256),
(90, 512),
(91, 1),
(92, 2),
(93, 4),
(94, 8),
(95, 16),
(96, 32),
(97, 64),
(98, 128),
(99, 256),
(100, 512);

CREATE TABLE `endoftestempty` (
  `id` int(11) NOT NULL,
  `name` mediumtext NOT NULL,
  `value` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `endoftestempty` (`id`, `name`, `value`) VALUES
(1, 'yes', 1),
(2, 'no', 0),
(3, 'maybe', 2),
(4, 'what', -1);

CREATE TABLE `endoftestwithfourentrys` (
  `id` int(11) NOT NULL,
  `value` mediumtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `endoftestwithupdates` (
  `id` int(11) NOT NULL,
  `username` mediumtext NOT NULL,
  `oldusername` mediumtext NOT NULL,
  `banned` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `endoftestwithupdates` (`id`, `username`, `oldusername`, `banned`) VALUES
(1, 'Madpeter', 'Madpeter', 0);

CREATE TABLE `flagedvalues` (
  `id` int(11) NOT NULL,
  `name` text NOT NULL,
  `value` varchar(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `flagedvalues` (`id`, `name`, `value`) VALUES
(1, 'asdasdasd', '1'),
(2, 'asdafsdfsdf', '0');

CREATE TABLE `liketests` (
  `id` int(11) NOT NULL,
  `name` mediumtext NOT NULL,
  `value` mediumtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `liketests` (`id`, `name`, `value`) VALUES
(1, 'redpondblue 1', 'pondbluered 1'),
(2, 'pondblue 2', 'pondblue 2'),
(3, 'Party Advent', 'Song'),
(4, 'Advent', 'wise');

CREATE TABLE `relationtestinga` (
  `id` int(11) NOT NULL,
  `name` mediumtext NOT NULL,
  `linkid` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `relationtestinga` (`id`, `name`, `linkid`) VALUES
(1, 'group1', 1),
(2, 'group2', 4);

CREATE TABLE `relationtestingb` (
  `id` int(11) NOT NULL,
  `extended1` mediumtext NOT NULL,
  `extended2` mediumtext NOT NULL,
  `extended3` mediumtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `relationtestingb` (`id`, `extended1`, `extended2`, `extended3`) VALUES
(1, 'a1', 'a2', 'a3'),
(2, 'b1', 'b2', 'b3'),
(3, 'd1', 'd2', 'd3'),
(4, 'c1', 'c2', 'c3');

CREATE TABLE `rollbacktest` (
  `id` int(11) NOT NULL,
  `name` text NOT NULL,
  `value` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `twintables1` (
  `id` int(11) NOT NULL,
  `title` mediumtext NOT NULL,
  `message` mediumtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `twintables1` (`id`, `title`, `message`) VALUES
(1, 'harry potter', 'is not very good');

CREATE TABLE `twintables2` (
  `id` int(11) NOT NULL,
  `title` mediumtext NOT NULL,
  `message` mediumtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `twintables2` (`id`, `title`, `message`) VALUES
(1, 'harry potter', 'is great');

CREATE TABLE `weirdtable` (
  `id` int(11) NOT NULL,
  `weirda` set('5','6','7','8') DEFAULT NULL,
  `weirdb` enum('1','2','3','4') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `weirdtable` (`id`, `weirda`, `weirdb`) VALUES
(-41, '5,6,7,8', '3'),
(1, '5', '4'),
(2, '7', '2');


ALTER TABLE `alltypestable`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `counttoonehundo`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `endoftestempty`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `endoftestwithfourentrys`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `endoftestwithupdates`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `flagedvalues`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `liketests`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `relationtestinga`
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `linkid` (`linkid`);

ALTER TABLE `relationtestingb`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `rollbacktest`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `twintables1`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `twintables2`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `weirdtable`
  ADD PRIMARY KEY (`id`);


ALTER TABLE `alltypestable`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `counttoonehundo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=101;

ALTER TABLE `endoftestempty`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

ALTER TABLE `endoftestwithfourentrys`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `endoftestwithupdates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `flagedvalues`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

ALTER TABLE `liketests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

ALTER TABLE `relationtestinga`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

ALTER TABLE `relationtestingb`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

ALTER TABLE `rollbacktest`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `twintables1`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `twintables2`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `weirdtable`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;


ALTER TABLE `relationtestinga`
  ADD CONSTRAINT `testingb_in_use` FOREIGN KEY (`linkid`) REFERENCES `relationtestingb` (`id`) ON UPDATE NO ACTION;
