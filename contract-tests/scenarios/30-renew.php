<?php

// Renewal vendors and proxy renew.
$client = ["avatarUUID" => "c11e0000-0000-4000-8000-000000000001", "avatarName" => "Contract Client"];
$other = ["avatarUUID" => "c11e0000-0000-4000-8000-000000000002", "avatarName" => "Other Client"];

return [
    "description" => "Renewal lookups, cost/time, renew payments and proxy renew",
    "seeds" => ["catalog"],
    "steps" => [
        ["id" => "details_client", "path" => "Renew/Details", "post" => ["avatarUUID" => $client["avatarUUID"]]],
        ["id" => "details_other", "path" => "Renew/Details", "post" => ["avatarUUID" => $other["avatarUUID"]]],
        ["id" => "details_no_rentals", "path" => "Renew/Details", "post" => ["avatarUUID" => "289c3e36-69b3-40c5-9229-0c6a5d230766"]],
        ["id" => "details_banned", "path" => "Renew/Details", "post" => ["avatarUUID" => "ba11ed00-0000-4000-8000-000000000005"]],
        ["id" => "details_unknown", "path" => "Renew/Details", "post" => ["avatarUUID" => "00000000-1111-4000-8000-000000000000"]],
        ["id" => "details_invalid", "path" => "Renew/Details", "post" => ["avatarUUID" => "nope"]],
        ["id" => "costandtime_week", "path" => "Renew/Costandtime", "post" => ["rentalUid" => "rentActv"]],
        ["id" => "costandtime_month_expired", "path" => "Renew/Costandtime", "post" => ["rentalUid" => "rentExpd"]],
        ["id" => "costandtime_soon", "path" => "Renew/Costandtime", "post" => ["rentalUid" => "rentSoon"]],
        ["id" => "costandtime_unknown", "path" => "Renew/Costandtime", "post" => ["rentalUid" => "nope0000"]],
        ["id" => "renewnow_missing_rental", "path" => "Renew/Renewnow", "post" => $client + ["amountpaid" => 500]],
        ["id" => "renewnow_unknown_rental", "path" => "Renew/Renewnow",
            "post" => $client + ["rentalUid" => "nope0000", "amountpaid" => 500]],
        ["id" => "renewnow_wrong_amount", "path" => "Renew/Renewnow",
            "post" => $client + ["rentalUid" => "rentActv", "amountpaid" => 123]],
        ["id" => "renewnow_banned_payer", "path" => "Renew/Renewnow",
            "post" => ["avatarUUID" => "ba11ed00-0000-4000-8000-000000000005", "avatarName" => "Banned Resident",
                "rentalUid" => "rentActv", "amountpaid" => 500]],
        ["id" => "renewnow_own", "path" => "Renew/Renewnow",
            "post" => $client + ["rentalUid" => "rentActv", "amountpaid" => 500]],
        ["id" => "renewnow_own_two_periods", "path" => "Renew/Renewnow",
            "post" => $client + ["rentalUid" => "rentSoon", "amountpaid" => 500]],
        ["id" => "renewnow_gift_for_other", "path" => "Renew/Renewnow",
            "post" => $client + ["rentalUid" => "rentExpd", "amountpaid" => 900]],
        ["id" => "renewnow_via_reseller", "path" => "Renew/Renewnow", "as" => "reseller",
            "post" => $other + ["rentalUid" => "rentExpd", "amountpaid" => 900]],
        ["id" => "details_client_after", "path" => "Renew/Details", "post" => ["avatarUUID" => $client["avatarUUID"]]],
        ["id" => "costandtime_after", "path" => "Renew/Costandtime", "post" => ["rentalUid" => "rentActv"]],
        ["id" => "proxy_by_name", "path" => "Proxyrenew/Details", "post" => ["targetuid" => "Contract Client"]],
        ["id" => "proxy_by_uuid", "path" => "Proxyrenew/Details", "post" => ["targetuid" => $other["avatarUUID"]]],
        ["id" => "proxy_single_name", "path" => "Proxyrenew/Details", "post" => ["targetuid" => "Contract"]],
        ["id" => "proxy_unknown_name", "path" => "Proxyrenew/Details", "post" => ["targetuid" => "Nobody Here"]],
        ["id" => "proxy_too_short", "path" => "Proxyrenew/Details", "post" => ["targetuid" => "ab"]],
        ["id" => "proxy_missing", "path" => "Proxyrenew/Details"],
    ],
];
