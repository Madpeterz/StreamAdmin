<?php

// HUD with every feature disabled (baseline config).
return [
    "description" => "HUD endpoints with details and renewals disabled",
    "seeds" => ["catalog"],
    "steps" => [
        ["id" => "hudconfig", "kind" => "hud", "path" => "Config/Hudconfig", "as" => "client"],
        ["id" => "rentals_details", "kind" => "hud", "path" => "Rentals/Details", "as" => "client"],
        ["id" => "rentals_costs", "kind" => "hud", "path" => "Rentals/Costs", "as" => "client", "post" => ["rentalUid" => "rentActv"]],
        ["id" => "rentals_renew", "kind" => "hud", "path" => "Rentals/Renew", "as" => "client",
            "post" => ["rentalUid" => "rentActv", "amountpaid" => 500, "transactionUUID" => "7a000000-0000-4000-8000-000000000001"]],
        ["id" => "details_resend", "kind" => "hud", "path" => "Details/Resend", "as" => "client", "post" => ["rentalUid" => "rentActv"]],
    ],
];
