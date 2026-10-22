<?php

// Rental vendor purchase flow on a vendor owned by the system owner.
$buyer = ["avatarUUID" => "b0b00000-0000-4000-8000-0000000000b0", "avatarName" => "Buyer Resident"];

return [
    "description" => "Vendor config, stock check and purchase (owner vendor)",
    "seeds" => ["catalog"],
    "steps" => [
        ["id" => "getconfig_owner", "path" => "Buy/Getconfig", "post" => ["packageuid" => "pkgShout", "texturepack" => 1]],
        ["id" => "getconfig_reseller", "path" => "Buy/Getconfig", "as" => "reseller",
            "post" => ["packageuid" => "pkgShout", "texturepack" => 1]],
        ["id" => "getconfig_soldout", "path" => "Buy/Getconfig", "post" => ["packageuid" => "pkgEmpty", "texturepack" => 1]],
        // fixed in 2.0.1.3: loadByPackageUid() result compared with false, unknown package answered ok with null textures
        ["id" => "getconfig_unknown_package", "path" => "Buy/Getconfig",
            "post" => ["packageuid" => "nope0000", "texturepack" => 1]],
        // fixed in 2.0.1.3: loadID() result compared with false, unknown texture pack answered ok with null textures
        ["id" => "getconfig_unknown_texturepack", "path" => "Buy/Getconfig",
            "post" => ["packageuid" => "pkgShout", "texturepack" => 99]],
        ["id" => "getconfig_stranger", "path" => "Buy/Getconfig", "as" => "stranger",
            "post" => ["packageuid" => "pkgShout", "texturepack" => 1]],
        ["id" => "checkstock_instock", "path" => "Buy/Checkstock", "post" => ["packageuid" => "pkgShout"]],
        ["id" => "checkstock_soldout", "path" => "Buy/Checkstock", "post" => ["packageuid" => "pkgEmpty"]],
        ["id" => "checkstock_needwork_only", "path" => "Buy/Checkstock", "post" => ["packageuid" => "pkgIce01"]],
        ["id" => "checkstock_unknown_package", "path" => "Buy/Checkstock", "post" => ["packageuid" => "nope0000"]],
        ["id" => "startrental_wrong_amount", "path" => "Buy/Startrental",
            "post" => $buyer + ["packageuid" => "pkgShout", "amountpaid" => 499]],
        ["id" => "startrental_unknown_package", "path" => "Buy/Startrental",
            "post" => $buyer + ["packageuid" => "nope0000", "amountpaid" => 500]],
        ["id" => "startrental_banned_avatar", "path" => "Buy/Startrental",
            "post" => ["avatarUUID" => "ba11ed00-0000-4000-8000-000000000005", "avatarName" => "Banned Resident",
                "packageuid" => "pkgShout", "amountpaid" => 500]],
        ["id" => "startrental_soldout", "path" => "Buy/Startrental",
            "post" => $buyer + ["packageuid" => "pkgEmpty", "amountpaid" => 250]],
        // fixed in 2.0.1.3: TypeError on invalid avatarUUID returns an empty HTTP 500
        ["id" => "startrental_invalid_avatar_uuid", "path" => "Buy/Startrental",
            "post" => ["avatarUUID" => "nope", "avatarName" => "Buyer Resident", "packageuid" => "pkgShout", "amountpaid" => 500]],
        ["id" => "startrental_two_periods", "path" => "Buy/Startrental",
            "post" => $buyer + ["packageuid" => "pkgShout", "amountpaid" => 1000]],
        ["id" => "startrental_now_soldout", "path" => "Buy/Startrental",
            "post" => $buyer + ["packageuid" => "pkgShout", "amountpaid" => 500]],
        ["id" => "checkstock_after_sale", "path" => "Buy/Checkstock", "post" => ["packageuid" => "pkgShout"]],
        ["id" => "hasrental_buyer", "path" => "Client/Hasrental", "post" => ["checkinguuid" => $buyer["avatarUUID"]]],
        ["id" => "renew_details_buyer", "path" => "Renew/Details", "post" => ["avatarUUID" => $buyer["avatarUUID"]],
            "capture" => ["buyer_rental" => ["dataset.0", "/^([^|]+)\|/"]]],
        ["id" => "costandtime_buyer", "path" => "Renew/Costandtime", "post" => ["rentalUid" => "{{buyer_rental}}"]],
        ["id" => "details_send_stranger_object", "path" => "Details/Send", "as" => "stranger",
            "object" => ["ownerkey" => $buyer["avatarUUID"], "ownername" => $buyer["avatarName"]],
            "post" => ["rentalUid" => "{{buyer_rental}}"]],
        ["id" => "details_send_owner", "path" => "Details/Send", "post" => ["rentalUid" => "{{buyer_rental}}"]],
        ["id" => "details_send_again", "path" => "Details/Send", "post" => ["rentalUid" => "{{buyer_rental}}"]],
        ["id" => "details_send_unknown", "path" => "Details/Send", "post" => ["rentalUid" => "nope0000"]],
        ["id" => "details_send_missing", "path" => "Details/Send"],
    ],
];
