<?php

namespace App\Endpoint\View\Outbox;

use App\R7\Set\ApirequestsSet;
use App\R7\Set\StreamSet;

class Api extends View
{
    public function process(): void
    {
        $this->output->addSwapTagString("page_title", " Pending api calls");
        $table_head = ["id","Event","Port","Last tryed","Attempts","message"];
        $table_body = [];
        $api_requests_set = new ApirequestsSet();
        $api_requests_set->loadAll();
        $stream_set = new StreamSet();
        $stream_set->loadByValues($api_requests_set->getUniqueArray("streamLink"));
        $event_names = [
        "optToggleStatus" => "Toggle state {Opt}",
        "optPasswordReset" => "Reset PWs {Opt}",
        "optAutodjNext" => "AutoDJ next {Opt}",
        "optToggleAutodj" => "Toggle AutoDJ {Opt}",
        "eventEnableStart" => "Enable {New}",
        "eventStartSyncUsername" => "Customize username {New}",
        "eventEnableRenew" => "Enable {Renew}",
        "eventDisableExpire" => "Disable {Expire}",
        "eventDisableRevoke" => "Disable {Revoke}",
        "eventResetPasswordRevoke" => "New PWs {Revoke}",
        "eventClearDjs" => "Clear DJs {Revoke}",
        "eventRevokeResetUsername" => "Reset username {Revoke}",
        "eventRecreateRevoke" => "Recreate account {Revoke}",
        "eventCreateStream" => "Create stream on server",
        "eventUpdateStream" => "Update stream on server",
        ];
        foreach ($api_requests_set as $request) {
            $stream = $stream_set->getObjectByID($request->getStreamLink());
            $table_body[] = [$request->getId(),$event_names[$request->getEventname()],
            $stream->getPort(),expiredAgo($request->getLastAttempt()),
            $request->getAttempts(),$request->getMessage()];
        }
        $this->setSwapTag("page_content", $this->renderDatatable($table_head, $table_body));
    }
}
