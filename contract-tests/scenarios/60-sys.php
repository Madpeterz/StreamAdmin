<?php

// sys.php: token = sha1(unixtime . method . action . httpInboundSecret).
return [
    "description" => "System API token checks",
    "seeds" => ["catalog"],
    "steps" => [
        ["id" => "notecard_nowork", "kind" => "sys", "path" => "Notecard/Next"],
        ["id" => "bad_token", "kind" => "sys", "path" => "Notecard/Next", "tamper" => ["hash" => "invalid"]],
        ["id" => "signed_with_sl_code", "kind" => "sys", "path" => "Notecard/Next", "tamper" => ["sign_with" => "sl"]],
        ["id" => "time_too_old", "kind" => "sys", "path" => "Notecard/Next", "tamper" => ["time_offset" => -300]],
        ["id" => "missing_token", "kind" => "sys", "path" => "Notecard/Next", "tamper" => ["omit" => ["token"]]],
        ["id" => "missing_method", "kind" => "sys", "path" => "Notecard/Next", "tamper" => ["omit" => ["method"]]],
        ["id" => "other_method_action", "kind" => "sys", "path" => "Notecard/Next", "method" => "Anything", "action" => "Else"],
        ["id" => "unknown_endpoint", "kind" => "sys", "path" => "Nope/Nothing"],
        // short lived keys the bot receives in FetchNextNotecard instead of the inbound secret
        ["id" => "scoped_key_current", "kind" => "sys", "path" => "Notecard/Next", "tamper" => ["sys_key" => 0]],
        ["id" => "scoped_key_previous_window", "kind" => "sys", "path" => "Notecard/Next", "tamper" => ["sys_key" => -1]],
        ["id" => "scoped_key_expired", "kind" => "sys", "path" => "Notecard/Next", "tamper" => ["sys_key" => -2]],
        ["id" => "scoped_key_other_action", "kind" => "sys", "path" => "Notecard/Next", "method" => "Anything", "action" => "Else",
            "tamper" => ["sys_key" => 0, "sys_key_for" => ["Notecard", "Next"]]],
    ],
];
