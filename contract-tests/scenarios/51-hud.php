<?php

// HUD worn by the renting client (ownerkey = the avatar wearing the HUD).
$client = ["avatarUUID" => "c11e0000-0000-4000-8000-000000000001", "avatarName" => "Contract Client"];

return [
    "description" => "HUD config, rental list, costs, details resend and renewals",
    "seeds" => ["catalog", "hud"],
    "steps" => [
        ["id" => "hudconfig", "kind" => "hud", "path" => "Config/Hudconfig", "as" => "client"],
        ["id" => "hudconfig_api_path_rejected", "kind" => "sl", "path" => "Config/Hudconfig", "as" => "client"],
        ["id" => "rentals_details", "kind" => "hud", "path" => "Rentals/Details", "as" => "client"],
        ["id" => "rentals_details_stranger", "kind" => "hud", "path" => "Rentals/Details", "as" => "stranger"],
        ["id" => "rentals_details_banned", "kind" => "hud", "path" => "Rentals/Details",
            "object" => ["ownerkey" => "ba11ed00-0000-4000-8000-000000000005", "ownername" => "Banned Resident"]],
        ["id" => "costs_own", "kind" => "hud", "path" => "Rentals/Costs", "as" => "client", "post" => ["rentalUid" => "rentActv"]],
        // fixed in 2.0.1.3: message contains a CRLF and source indentation from a multi-line PHP string
        ["id" => "costs_other", "kind" => "hud", "path" => "Rentals/Costs", "as" => "client", "post" => ["rentalUid" => "rentExpd"]],
        ["id" => "costs_unknown", "kind" => "hud", "path" => "Rentals/Costs", "as" => "client", "post" => ["rentalUid" => "nope0000"]],
        ["id" => "resend_own", "kind" => "hud", "path" => "Details/Resend", "as" => "client", "post" => ["rentalUid" => "rentActv"]],
        ["id" => "resend_own_again", "kind" => "hud", "path" => "Details/Resend", "as" => "client", "post" => ["rentalUid" => "rentActv"]],
        ["id" => "resend_other", "kind" => "hud", "path" => "Details/Resend", "as" => "client", "post" => ["rentalUid" => "rentExpd"]],
        ["id" => "renew_missing_transaction", "kind" => "hud", "path" => "Rentals/Renew", "as" => "client",
            "post" => $client + ["rentalUid" => "rentActv", "amountpaid" => 500]],
        ["id" => "renew_wrong_amount", "kind" => "hud", "path" => "Rentals/Renew", "as" => "client",
            "post" => $client + ["rentalUid" => "rentActv", "amountpaid" => 1, "transactionUUID" => "7a000000-0000-4000-8000-000000000001"]],
        ["id" => "renew_other_rental", "kind" => "hud", "path" => "Rentals/Renew", "as" => "client",
            "post" => $client + ["rentalUid" => "rentExpd", "amountpaid" => 900, "transactionUUID" => "7a000000-0000-4000-8000-000000000002"]],
        ["id" => "renew_own", "kind" => "hud", "path" => "Rentals/Renew", "as" => "client",
            "post" => $client + ["rentalUid" => "rentActv", "amountpaid" => 500, "transactionUUID" => "7a000000-0000-4000-8000-000000000003"]],
        ["id" => "rentals_details_after", "kind" => "hud", "path" => "Rentals/Details", "as" => "client"],
    ],
];
