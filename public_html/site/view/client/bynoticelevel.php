<?php
$package_set = new package_set();
$package_set->loadAll();
$rental_set = new rental_set();
$rental_set->load_on_field("noticelink",$page);
$avatar_set = new avatar_set();
$avatar_set->load_ids($rental_set->get_all_by_field("avatarlink"));
$stream_set = new stream_set();
$stream_set->load_ids($rental_set->get_all_by_field("streamlink"));
$notice = new notice();
$notice->load($page);
$template_parts["page_title"] .= " By notice level: ".$notice->get_name()."";

$table_head = array("id","Rental UID","Avatar","Package","Port","Timeleft/Expired");
$table_body = array();

foreach($rental_set->get_all_ids() as $rental_id)
{
    $rental = $rental_set->get_object_by_id($rental_id);
    $avatar = $avatar_set->get_object_by_id($rental->get_avatarlink());
    $stream = $stream_set->get_object_by_id($rental->get_streamlink());
    $package = $package_set->get_object_by_id($stream->get_packagelink());
    $entry = array();
    $entry[] = $rental->get_id();
    $entry[] = '<a href="[[url_base]]client/manage/'.$rental->get_rental_uid().'">'.$rental->get_rental_uid().'</a>';
    $av_detail = explode(" ",$avatar->get_avatarname());
    if($av_detail[1] != "Resident") $entry[] = $avatar->get_avatarname();
    else $entry[] = $av_detail[0];
    $entry[] = $package->get_name();
    $entry[] = $stream->get_port();
    if($rental->get_expireunixtime() > time())
    {
        $entry[] = "Active - ".timeleft_hours_and_days($rental->get_expireunixtime());
    }
    else
    {
        $entry[] = "Expired - ".expired_ago($rental->get_expireunixtime());
    }
    $table_body[] = $entry;
}
echo render_datatable($table_head,$table_body);
?>
