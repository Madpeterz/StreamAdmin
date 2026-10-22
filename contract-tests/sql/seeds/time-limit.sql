-- Rental time limit enforced (max 20 days remaining).
UPDATE `slconfig` SET `limitTime` = 1, `maxStreamTimeDays` = 20 WHERE `id` = 1;
