USE `r4test`;

DROP TABLE IF EXISTS `items`;
CREATE TABLE `items` (
  `id` int(11) NOT NULL,
  `packageid` int(11) NOT NULL,
  `sold` int(11) NOT NULL DEFAULT 0,
  `streamurl` text NOT NULL,
  `streamport` text NOT NULL,
  `streampassword` text NOT NULL,
  `baditem` int(11) NOT NULL DEFAULT 0,
  `adminurl` longtext DEFAULT NULL,
  `adminusername` longtext DEFAULT NULL,
  `adminpassword` longtext DEFAULT NULL,
  `serverlocid` int(11) NOT NULL DEFAULT 1,
  `addon1` text NOT NULL,
  `addon2` text NOT NULL,
  `addon3` text NOT NULL,
  `addon4` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `items` (`id`, `packageid`, `sold`, `streamurl`, `streamport`, `streampassword`, `baditem`, `adminurl`, `adminusername`, `adminpassword`, `serverlocid`, `addon1`, `addon2`, `addon3`, `addon4`) VALUES
(1, 1, 1, 'http://server28.test', '5151', 'asdasd', 0, 'http://server27.test/login.php', 'adminuser', 'thisadminpasswd', 1, '', '', '', '');

DROP TABLE IF EXISTS `packages`;
CREATE TABLE `packages` (
  `id` int(11) NOT NULL,
  `name` text NOT NULL,
  `streamtype` text NOT NULL,
  `streamrate` text NOT NULL,
  `users` float NOT NULL,
  `Lcost` float NOT NULL,
  `sublength` int(11) NOT NULL DEFAULT 31,
  `maintexture` text NOT NULL,
  `infotexture` text NOT NULL,
  `soldouttexture` text NOT NULL,
  `autoDJ` int(11) NOT NULL DEFAULT 0,
  `use_addon_field_1` int(11) NOT NULL DEFAULT 0,
  `use_addon_field_2` int(11) NOT NULL DEFAULT 0,
  `use_addon_field_3` int(11) NOT NULL DEFAULT 0,
  `use_addon_field_4` int(11) NOT NULL DEFAULT 0,
  `addon_field_1` text NOT NULL,
  `addon_field_2` text NOT NULL,
  `addon_field_3` text NOT NULL,
  `addon_field_4` text NOT NULL,
  `addon_field1_default` text NOT NULL,
  `addon_field2_default` text NOT NULL,
  `addon_field3_default` text NOT NULL,
  `addon_field4_default` text NOT NULL,
  `enable_ans` int(11) NOT NULL DEFAULT 0,
  `ans_product_id` int(11) NOT NULL,
  `enable_invites` int(1) NOT NULL DEFAULT 0,
  `use_vender_config_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `packages` (`id`, `name`, `streamtype`, `streamrate`, `users`, `Lcost`, `sublength`, `maintexture`, `infotexture`, `soldouttexture`, `autoDJ`, `use_addon_field_1`, `use_addon_field_2`, `use_addon_field_3`, `use_addon_field_4`, `addon_field_1`, `addon_field_2`, `addon_field_3`, `addon_field_4`, `addon_field1_default`, `addon_field2_default`, `addon_field3_default`, `addon_field4_default`, `enable_ans`, `ans_product_id`, `enable_invites`, `use_vender_config_id`) VALUES
(1, 'testimportissue28', 'shoutcast', '128', 33, 444, 31, '00000000-0000-0000-0000-000000000003', '00000000-0000-0000-0000-000000000002', '00000000-0000-0000-0000-000000000003', 0, 0, 0, 0, 0, '', '', '', '', '', '', '', '', 0, 1, 0, 1);

DROP TABLE IF EXISTS `sales_tracking`;
CREATE TABLE `sales_tracking` (
  `id` int(11) NOT NULL,
  `resellerid` int(11) NOT NULL,
  `venderid` int(11) NOT NULL,
  `userid` int(11) NOT NULL,
  `salemode` int(11) NOT NULL,
  `amount` int(11) NOT NULL,
  `date` text NOT NULL,
  `time` text NOT NULL,
  `SLname` text DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

INSERT INTO `sales_tracking` (`id`, `resellerid`, `venderid`, `userid`, `salemode`, `amount`, `date`, `time`, `SLname`) VALUES
(1, 1, 1, 1, 1, 2334, '2021/21/03', '11:44:33', 'Madpeter28 Zond28');

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `itemid` float NOT NULL,
  `slkey` text NOT NULL,
  `slname` text NOT NULL,
  `buyfromboxkey` text NOT NULL,
  `venderlanddetail` text NOT NULL,
  `noticesent` int(11) NOT NULL DEFAULT 6,
  `notes` text DEFAULT NULL,
  `expireunix` bigint(20) NOT NULL DEFAULT 0,
  `packageid` int(11) NOT NULL DEFAULT 0,
  `locktoreseller` int(11) NOT NULL DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `users` (`id`, `itemid`, `slkey`, `slname`, `buyfromboxkey`, `venderlanddetail`, `noticesent`, `notes`, `expireunix`, `packageid`, `locktoreseller`) VALUES
(1, 1, '40000000-0000-0000-2800-000000000000', 'Madpeter28 Zond28', '50000000-0000-2800-0000-000000000000', 'lol', 6, 'this is a note', 2002909420, 1, 1);


ALTER TABLE `items`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `packages`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `sales_tracking`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);


ALTER TABLE `items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `packages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `sales_tracking`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;