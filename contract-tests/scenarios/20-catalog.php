<?php

// Read-only vendor endpoints: stock, texture packs, tree vendors and client lookups.
return [
    "description" => "Stock levels, texture packs, tree vendors, client rental check",
    "seeds" => ["catalog"],
    "steps" => [
        // fixed in 2.0.1.3: free streams were matched with rentalLink = NULL, so every stock level was 0
        ["id" => "stocklevel", "path" => "Stock/Stocklevel"],
        ["id" => "stocklevel_reseller", "path" => "Stock/Stocklevel", "as" => "reseller"],
        ["id" => "texturepack_owner", "path" => "Texturepack/Getpack", "post" => ["texturepack" => 1]],
        ["id" => "texturepack_reseller", "path" => "Texturepack/Getpack", "as" => "reseller", "post" => ["texturepack" => 1]],
        ["id" => "texturepack_unknown", "path" => "Texturepack/Getpack", "post" => ["texturepack" => 99]],
        ["id" => "texturepack_missing", "path" => "Texturepack/Getpack"],
        ["id" => "tree_packages", "path" => "Tree/Getpackages", "post" => ["tree_vender_id" => 1]],
        ["id" => "tree_empty", "path" => "Tree/Getpackages", "post" => ["tree_vender_id" => 2]],
        // fixed in 2.0.1.3: loadID() result compared with false, unknown tree vendor crashes with an empty HTTP 500
        ["id" => "tree_unknown", "path" => "Tree/Getpackages", "post" => ["tree_vender_id" => 99]],
        ["id" => "tree_missing", "path" => "Tree/Getpackages"],
        ["id" => "hasrental_some", "path" => "Client/Hasrental", "post" => ["checkinguuid" => "c11e0000-0000-4000-8000-000000000001"]],
        ["id" => "hasrental_expired_only", "path" => "Client/Hasrental",
            "post" => ["checkinguuid" => "c11e0000-0000-4000-8000-000000000002"]],
        ["id" => "hasrental_no_streams", "path" => "Client/Hasrental",
            "post" => ["checkinguuid" => "289c3e36-69b3-40c5-9229-0c6a5d230766"]],
        ["id" => "hasrental_unknown", "path" => "Client/Hasrental",
            "post" => ["checkinguuid" => "00000000-1111-4000-8000-000000000000"]],
        ["id" => "hasrental_missing", "path" => "Client/Hasrental"],
        ["id" => "ping_reseller", "path" => "Object/Ping", "as" => "reseller"],
    ],
];
