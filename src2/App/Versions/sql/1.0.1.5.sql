UPDATE `slconfig` SET `db_version` = '1.0.1.6' WHERE `slconfig`.`id` = 1;
RENAME TABLE `api_requests` TO `apirequests`;
RENAME TABLE `notice_notecard` TO `noticenotecard`;
RENAME TABLE `treevender_packages` TO `treevenderpackages`; 