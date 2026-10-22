<?php

// Request validation shared by every api.php / hud.php endpoint, exercised through Object/Ping.
return [
    "description" => "Hash, time window, shard, required fields, version and routing checks",
    "seeds" => [],
    "steps" => [
        ["id" => "ping_owner", "kind" => "sl", "path" => "Object/Ping", "as" => "owner"],
        ["id" => "ping_lowercase_path", "kind" => "sl", "path" => "object/ping", "as" => "owner"],
        ["id" => "ping_stranger_not_reseller", "kind" => "sl", "path" => "Object/Ping", "as" => "stranger"],
        ["id" => "bad_hash", "kind" => "sl", "path" => "Object/Ping", "tamper" => ["hash" => "invalid"]],
        ["id" => "signed_with_hud_code", "kind" => "sl", "path" => "Object/Ping", "tamper" => ["sign_with" => "hud"]],
        ["id" => "time_too_old", "kind" => "sl", "path" => "Object/Ping", "tamper" => ["time_offset" => -300]],
        ["id" => "time_too_new", "kind" => "sl", "path" => "Object/Ping", "tamper" => ["time_offset" => 300]],
        ["id" => "time_inside_window", "kind" => "sl", "path" => "Object/Ping", "tamper" => ["time_offset" => -90]],
        ["id" => "wrong_shard", "kind" => "sl", "path" => "Object/Ping", "tamper" => ["shard" => "Testing"]],
        ["id" => "missing_shard", "kind" => "sl", "path" => "Object/Ping", "tamper" => ["shard" => null]],
        ["id" => "missing_ownerkey", "kind" => "sl", "path" => "Object/Ping", "tamper" => ["omit" => ["ownerkey"]]],
        ["id" => "missing_hash", "kind" => "sl", "path" => "Object/Ping", "tamper" => ["omit" => ["hash"]]],
        ["id" => "missing_unixtime", "kind" => "sl", "path" => "Object/Ping", "tamper" => ["omit" => ["unixtime"]]],
        ["id" => "invalid_uuid", "kind" => "sl", "path" => "Object/Ping", "object" => ["objectuuid" => "not-a-uuid"]],
        ["id" => "old_version", "kind" => "sl", "path" => "Object/Ping", "object" => ["version" => "2.0.0.9"]],
        ["id" => "unknown_endpoint", "kind" => "sl", "path" => "Nope/Nothing"],
        ["id" => "empty_post", "kind" => "raw", "method" => "POST", "path" => "/api.php/Object/Ping"],
        ["id" => "hud_signed_with_sl_code", "kind" => "hud", "path" => "Config/Hudconfig", "as" => "stranger",
            "tamper" => ["sign_with" => "sl"]],
        ["id" => "hud_config", "kind" => "hud", "path" => "Config/Hudconfig", "as" => "stranger"],
    ],
];
