<?php

namespace App\View\Outbox;

use App\Models\ApirequestsSet;
use App\Models\StreamSet;

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
        $stream_set->loadIds($api_requests_set->getUniqueArray("streamlink"));
        $event_names = [
        "opt_toggle_status" => "Toggle state {Opt}",
        "opt_password_reset" => "Reset PWs {Opt}",
        "opt_autodj_next" => "AutoDJ next {Opt}",
        "opt_toggle_autodj" => "Toggle AutoDJ {Opt}",
        "event_enable_start" => "Enable {New}",
        "event_start_sync_username" => "Customize username {New}",
        "event_enable_renew" => "Enable {Renew}",
        "event_disable_expire" => "Disable {Expire}",
        "event_disable_revoke" => "Disable {Revoke}",
        "event_reset_password_revoke" => "New PWs {Revoke}",
        "event_clear_djs" => "Clear DJs {Revoke}",
        "event_revoke_reset_username" => "Reset username {Revoke}",
        "event_recreate_revoke" => "Recreate account {Revoke}",
        "event_create_stream" => "Create stream on server",
        "event_update_stream" => "Update stream on server",
        ];
        foreach ($api_requests_set->getAllIds() as $request_id) {
            $request = $api_requests_set->getObjectByID($request_id);
            $stream = $stream_set->getObjectByID($request->getStreamlink());
            $table_body[] = [$request->getId(),$event_names[$request->getEventname()],
            $stream->getPort(),expired_ago($request->getLast_attempt()),
            $request->getAttempts(),$request->getMessage()];
        }
        $this->output->setSwapTagString("page_content", render_datatable($table_head, $table_body));
    }
}
