<?php

$table_head = array("id","Rental UID","Avatar","Port","Notecard","Timeleft/Expired","Renewals");
$table_body = [];

foreach ($rental_set->get_all_ids() as $rental_id) {
    $rental = $rental_set->get_object_by_id($rental_id);
    $avatar = $avatar_set->get_object_by_id($rental->get_avatarlink());
    $stream = $stream_set->get_object_by_id($rental->get_streamlink());
    $entry = [];
    $entry[] = $rental->get_id();
    $entry[] = '<a href="[[url_base]]client/manage/' . $rental->get_rental_uid() . '">' . $rental->get_rental_uid() . '</a>';
    $av_detail = explode(" ", $avatar->get_avatarname());
    if ($av_detail[1] != "Resident") {
        $entry[] = $avatar->get_avatarname();
    } else {
        $entry[] = $av_detail[0];
    }
    $entry[] = $stream->get_port();
    $entry[] = "<button type=\"button\" class=\"btn btn-sm btn-outline-light\" data-toggle=\"modal\" data-target=\"#NotecardModal\" data-rentaluid=\"" . $rental->get_rental_uid() . "\">View</button>";
    if ($rental->get_expireunixtime() > time()) {
        $entry[] = "Active - " . timeleft_hours_and_days($rental->get_expireunixtime());
    } else {
        $entry[] = "Expired - " . expired_ago($rental->get_expireunixtime());
    }
    $entry[] = $rental->get_renewals();
    $table_body[] = $entry;
}
$view_reply->add_swap_tag_string("page_content", render_datatable($table_head, $table_body));
