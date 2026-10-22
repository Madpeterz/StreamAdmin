-- HTTP inbound secret shorter than the 5 character minimum.
UPDATE `slconfig` SET `httpInboundSecret` = 'abc' WHERE `id` = 1;
