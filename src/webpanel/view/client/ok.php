<?php
$whereconfig = array(
    "fields" => array("expireunixtime"),
    "values" => array(time()+$unixtime_day),
    "types" => array("i"),
    "matches" => array(">"),
);
$view_reply->add_swap_tag_string("page_title"," With status: Active");
include "webpanel/view/client/with_status.php";
?>
