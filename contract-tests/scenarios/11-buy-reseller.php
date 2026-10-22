<?php

// Purchase through an approved reseller's vendor: owner payment split is returned to the vendor.
return [
    "description" => "Purchase on a reseller vendor (owner payment split)",
    "seeds" => ["catalog"],
    "steps" => [
        ["id" => "startrental_via_reseller", "path" => "Buy/Startrental", "as" => "reseller",
            "post" => ["avatarUUID" => "b0b00000-0000-4000-8000-0000000000b0", "avatarName" => "Buyer Resident",
                "packageuid" => "pkgShout", "amountpaid" => 500]],
        ["id" => "stocklevel_after", "path" => "Stock/Stocklevel"],
    ],
];
