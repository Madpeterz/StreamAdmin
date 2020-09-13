<?php
$template_parts["page_title"] .= " Pending api calls";
$table_head = array("id","Event","Port","Last tryed","Attempts","message");
$table_body = array();
$api_requests_set = new api_requests_set();
$api_requests_set->loadAll();
$stream_set = new stream_set();
$stream_set->load_ids($api_requests_set->get_unique_array("streamlink"));
$event_names = array(
    "opt_toggle_status" => "Toggle state",
    "opt_password_reset" => "Reset PWs",
    "opt_autodj_next" => "AutoDJ next",
    "opt_toggle_autodj" => "Toggle AutoDJ",
    "event_enable_start" => "Enable {New}",
    "event_start_sync_username" => "Change username",
    "event_enable_renew" => "Enable {Renew}",
    "event_disable_expire" => "Disable {Expire}",
    "event_disable_revoke" => "Disable {Revoke}",
    "event_reset_password_revoke" => "New PWs {Revoke}",
    "event_clear_djs" => "Clear DJs {Revoke}",
);
foreach($api_requests_set->get_all_ids() as $request_id)
{
    $request = $api_requests_set->get_object_by_id($request_id);
    $stream = $stream_set->get_object_by_id($request->get_streamlink());
    $table_body[] = array($request->get_id(),$event_names[$request->get_eventname()],
    $stream->get_port(),expired_ago($request->get_last_attempt()),
    $request->get_attempts(),$request->get_message());
}
echo render_datatable($table_head,$table_body);
?>
