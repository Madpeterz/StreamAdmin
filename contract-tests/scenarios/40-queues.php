<?php

// Owner-only server objects that drain work queues (bot IMs and notecards disabled).
$repeat = function (string $id, string $path, int $times, array $extra = []): array {
    $steps = [];
    for ($i = 1; $i <= $times; $i++) {
        $steps[] = ["id" => $id . "_" . $i, "path" => $path] + $extra;
    }
    return $steps;
};

return [
    "description" => "Event, mail, notecard mail, details, bot command and notice queues",
    "seeds" => ["catalog", "queues"],
    "steps" => array_merge(
        [
            ["id" => "eventq_reseller_denied", "path" => "Eventq/Next", "as" => "reseller"],
            ["id" => "mailserver_reseller_denied", "path" => "Mailserver/Next", "as" => "reseller"],
            ["id" => "detailsserver_reseller_denied", "path" => "Detailsserver/Next", "as" => "reseller"],
            ["id" => "botcommandq_reseller_denied", "path" => "Botcommandq/Next", "as" => "reseller"],
            ["id" => "noticeserver_reseller_denied", "path" => "Noticeserver/Next", "as" => "reseller"],
            ["id" => "servercode_reseller_denied", "path" => "Object/Severrequestcode", "as" => "reseller"],
            ["id" => "servercode_owner", "path" => "Object/Severrequestcode"],
        ],
        $repeat("eventq", "Eventq/Next", 3),
        [
            ["id" => "notecardmail_1", "path" => "Noticeserver/Notecardmail"],
            ["id" => "notecardmail_2", "path" => "Noticeserver/Notecardmail"],
            // fixed in 2.0.1.3: bad notecard mail entry is removed then rolled back (status 0), queue stalls on it
            ["id" => "notecardmail_3", "path" => "Noticeserver/Notecardmail"],
            // fixed in 2.0.1.3: bad notecard mail entry is removed then rolled back (status 0), queue stalls on it
            ["id" => "notecardmail_4", "path" => "Noticeserver/Notecardmail"],
            // fixed in 2.0.1.3: no owner check: any approved reseller object could drain the notecard mail queue
            ["id" => "notecardmail_reseller_denied", "path" => "Noticeserver/Notecardmail", "as" => "reseller"],
        ],
        $repeat("detailsserver", "Detailsserver/Next", 2),
        $repeat("noticeserver", "Noticeserver/Next", 3),
        $repeat("mailserver", "Mailserver/Next", 5),
        $repeat("botcommandq_a", "Botcommandq/Next", 1),
        $repeat("mailserver_after_bot_a", "Mailserver/Next", 2),
        $repeat("botcommandq_b", "Botcommandq/Next", 1),
        $repeat("mailserver_after_bot_b", "Mailserver/Next", 2),
        $repeat("botcommandq_c", "Botcommandq/Next", 1),
    ),
];
