--- if you are running a testing version of the code before the installer was made please run this to join the update process.
ALTER TABLE `slconfig` ADD `publiclinkcode` VARCHAR(12) NULL AFTER `sllinkcode`;
ALTER TABLE `slconfig` ADD `db_version` VARCHAR(12) NOT NULL DEFAULT 'install' AFTER `id`;
