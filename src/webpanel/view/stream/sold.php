<?php

$view_reply->add_swap_tag_string("page_title", " With status: Sold");
$server_set = new server_set();
$server_set->loadAll();
$whereconfig = array(
    "fields" => array("rentallink"),
    "values" => array(null),
    "types" => array("s"),
    "matches" => array("IS NOT"),
);
include "webpanel/view/stream/with_status.php";
