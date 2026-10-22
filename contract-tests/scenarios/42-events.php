<?php

// Events API payloads (eventMessage is a JSON string consumed by external event servers).
return [
    "description" => "Event payloads for rental start, renew and expire",
    "seeds" => ["catalog", "queues", "events"],
    "steps" => [
        ["id" => "drain_seed_event_1", "path" => "Eventq/Next"],
        ["id" => "drain_seed_event_2", "path" => "Eventq/Next"],
        ["id" => "startrental", "path" => "Buy/Startrental",
            "post" => ["avatarUUID" => "b0b00000-0000-4000-8000-0000000000b0", "avatarName" => "Buyer Resident",
                "packageuid" => "pkgShout", "amountpaid" => 500]],
        // new rental uid is random
        ["id" => "event_rental_start", "path" => "Eventq/Next", "mask" => ["eventMessage.\$json.uid"]],
        ["id" => "renewnow", "path" => "Renew/Renewnow",
            "post" => ["avatarUUID" => "c11e0000-0000-4000-8000-000000000001", "avatarName" => "Contract Client",
                "rentalUid" => "rentActv", "amountpaid" => 500]],
        ["id" => "event_rental_renew", "path" => "Eventq/Next"],
        ["id" => "noticeserver_1", "path" => "Noticeserver/Next"],
        ["id" => "noticeserver_2", "path" => "Noticeserver/Next"],
        ["id" => "event_after_notices_1", "path" => "Eventq/Next"],
        ["id" => "event_after_notices_2", "path" => "Eventq/Next"],
        ["id" => "event_empty", "path" => "Eventq/Next"],
    ],
];
