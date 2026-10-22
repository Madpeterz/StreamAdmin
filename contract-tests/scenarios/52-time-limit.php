<?php

// Rental time limit (20 days max): HUD renewals are switched off and vendor renewals are capped.
$client = ["avatarUUID" => "c11e0000-0000-4000-8000-000000000001", "avatarName" => "Contract Client"];

return [
    "description" => "Rental time limit enforcement on HUD and vendor renewals",
    "seeds" => ["catalog", "hud", "time-limit"],
    "steps" => [
        ["id" => "hudconfig", "kind" => "hud", "path" => "Config/Hudconfig", "as" => "client"],
        ["id" => "hud_renew_blocked", "kind" => "hud", "path" => "Rentals/Renew", "as" => "client",
            "post" => $client + ["rentalUid" => "rentActv", "amountpaid" => 500, "transactionUUID" => "7a000000-0000-4000-8000-000000000001"]],
        ["id" => "vendor_renew_ok", "path" => "Renew/Renewnow", "post" => $client + ["rentalUid" => "rentActv", "amountpaid" => 500]],
        ["id" => "vendor_renew_over_limit", "path" => "Renew/Renewnow", "post" => $client + ["rentalUid" => "rentActv", "amountpaid" => 500]],
        ["id" => "vendor_renew_month_over_limit", "path" => "Renew/Renewnow",
            "post" => ["avatarUUID" => "c11e0000-0000-4000-8000-000000000002", "avatarName" => "Other Client",
                "rentalUid" => "rentExpd", "amountpaid" => 900]],
    ],
];
