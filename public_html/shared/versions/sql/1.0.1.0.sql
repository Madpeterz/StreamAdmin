UPDATE `slconfig` SET `db_version` = '1.0.1.1' WHERE `slconfig`.`id` = 1;
CREATE TABLE `timezones` (
  `id` int(11) NOT NULL,
  `name` varchar(125) NOT NULL,
  `code` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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


ALTER TABLE `timezones`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);


ALTER TABLE `timezones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;
ALTER TABLE `slconfig` ADD `displaytimezonelink` INT NOT NULL DEFAULT '11' AFTER `smtp_replyto`;

ALTER TABLE `slconfig` ADD `api_default_email` TEXT NOT NULL AFTER `displaytimezonelink`;

UPDATE `slconfig` SET `api_default_email` = 'noone@no.email.com' WHERE `slconfig`.`id` = 1;
