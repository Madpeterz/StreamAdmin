<?php

// Bot enabled for IMs, notecards and group invites; notecards are collected through sys.php.
$repeat = function (string $id, array $step, int $times): array {
    $steps = [];
    for ($i = 1; $i <= $times; $i++) {
        $steps[] = ["id" => $id . "_" . $i] + $step;
    }
    return $steps;
};

return [
    "description" => "Bot IM/notecard/invite flow, notecard sync and system notecard API",
    "seeds" => ["catalog", "queues", "bot"],
    "steps" => array_merge(
        [
            ["id" => "notecardsync_reseller_denied", "path" => "Bot/Notecardsync", "as" => "reseller"],
            ["id" => "notecardsync_idle", "path" => "Bot/Notecardsync"],
            ["id" => "detailsserver", "path" => "Detailsserver/Next"],
            ["id" => "notecardsync_pending", "path" => "Bot/Notecardsync"],
            ["id" => "sys_notecard_details", "kind" => "sys", "path" => "Notecard/Next"],
            ["id" => "sys_notecard_empty", "kind" => "sys", "path" => "Notecard/Next"],
            ["id" => "notecardsync_after", "path" => "Bot/Notecardsync"],
        ],
        $repeat("noticeserver", ["path" => "Noticeserver/Next"], 3),
        [
            ["id" => "sys_notecard_notice", "kind" => "sys", "path" => "Notecard/Next"],
            ["id" => "startrental_group_invite", "path" => "Buy/Startrental",
                "post" => ["avatarUUID" => "b0b00000-0000-4000-8000-0000000000b0", "avatarName" => "Buyer Resident",
                    "packageuid" => "pkgShout", "amountpaid" => 500]],
        ],
        $repeat("mail", ["path" => "Mailserver/Next"], 4),
        // each bot command leaves a message for the bot avatar that must be collected before the next one
        ...array_map(fn ($i) => [
            ["id" => "botcommandq_" . $i, "path" => "Botcommandq/Next"],
            ["id" => "mail_for_bot_" . $i, "path" => "Mailserver/Next"],
        ], range(1, 8)),
    ),
];
