UPDATE `slconfig` SET `db_version` = '1.0.1.6' WHERE `slconfig`.`id` = 1;
RENAME TABLE `streamadmin`.`api_requests` TO `streamadmin`.`apirequests`;
RENAME TABLE `streamadmin`.`notice_notecard` TO `streamadmin`.`noticenotecard`;
RENAME TABLE `streamadmin`.`treevender_packages` TO `streamadmin`.`treevenderpackages`; 