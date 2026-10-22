<?php

// Purchase by an avatar holding prepaid credits on the system owner's vendor: credits are refunded.
return [
    "description" => "Purchase with prepaid credits (owner vendor)",
    "seeds" => ["catalog"],
    "steps" => [
        ["id" => "startrental_with_credits", "path" => "Buy/Startrental",
            "post" => ["avatarUUID" => "c4ed1700-0000-4000-8000-000000000006", "avatarName" => "Credit Holder",
                "packageuid" => "pkgShout", "amountpaid" => 500]],
    ],
];
