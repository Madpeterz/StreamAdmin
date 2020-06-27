<?php
$template_parts["page_actions"] = "";
$rental_set = new rental_set();
$rental_set->load_with_config($whereconfig);
$avatar_set = new avatar_set();
$avatar_set->load_ids($rental_set->get_all_by_field("avatarlink"));
$stream_set = new stream_set();
$stream_set->load_ids($rental_set->get_all_by_field("streamlink"));
$package_set = new package_set();
$package_set->load_ids($stream_set->get_all_by_field("packagelink"));
$notice_set = new notice_set();
$notice_set->load_ids($rental_set->get_all_by_field("noticelink"));

$table_head = array("id","Rental UID","Avatar","Package","Port","Timeleft","NoticeLevel");
$table_body = array();

foreach($rental_set->get_all_ids() as $rental_id)
{
    $rental = $rental_set->get_object_by_id($rental_id);
    $avatar = $avatar_set->get_object_by_id($rental->get_avatarlink());
    $stream = $stream_set->get_object_by_id($rental->get_streamlink());
    $package = $package_set->get_object_by_id($stream->get_packagelink());
    $notice = $notice_set->get_object_by_id($rental->get_noticelink());
    $entry = array();
    $entry[] = $rental->get_id();
    $entry[] = '<a href="[[url_base]]client/manage/'.$rental->get_rental_uid().'">'.$rental->get_rental_uid().'</a>';
    $entry[] = explode(" ",$avatar->get_avatarname())[0];
    if($package == null) $entry[] = "Err";
    else $entry[] = $package->get_name();
    $entry[] = $stream->get_port();
    $entry[] = timeleft_hours_and_days($rental->get_expireunixtime());
    $entry[] = $notice->get_name();
    $table_body[] = $entry;
}
echo render_datatable($table_head,$table_body);
?>
