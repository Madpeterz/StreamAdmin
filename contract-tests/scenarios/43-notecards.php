<?php

// Static notecard inventory sync from the notice server object (comma separated inventory names).
return [
    "description" => "Static notecard inventory sync",
    "seeds" => ["catalog", "queues"],
    "steps" => [
        ["id" => "sync_add_new", "path" => "Noticeserver/Updatenotecards",
            "post" => ["notecards" => "Contract welcome,Contract setup,Brand new card"]],
        ["id" => "sync_drop_two", "path" => "Noticeserver/Updatenotecards", "post" => ["notecards" => "Contract welcome,none"]],
        ["id" => "sync_found_again", "path" => "Noticeserver/Updatenotecards",
            "post" => ["notecards" => "Contract welcome,Contract setup,Contract missing card"]],
        ["id" => "sync_empty_inventory", "path" => "Noticeserver/Updatenotecards", "post" => ["notecards" => ""]],
        ["id" => "sync_missing_field", "path" => "Noticeserver/Updatenotecards"],
        // fixed in 2.0.1.3: no owner check: any approved reseller object could rewrite the static notecard list
        ["id" => "sync_reseller", "path" => "Noticeserver/Updatenotecards", "as" => "reseller",
            "post" => ["notecards" => "Contract welcome"]],
        ["id" => "notecardmail_after_sync", "path" => "Noticeserver/Notecardmail"],
    ],
];
