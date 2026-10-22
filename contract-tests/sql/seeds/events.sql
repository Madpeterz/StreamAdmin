-- Events API enabled: rental start/renew/expire are queued for the event server object.
UPDATE `slconfig` SET `eventsAPI` = 1 WHERE `id` = 1;
