<?php

$objects = new objects();
$owner_objects_list = array(
    "apirequests",
    "mailserver",
    "noticeserver",
    "detailsserver",
    "notecardsserver"
);
$one_hour_ago = (time() - $unixtime_hour);
$objects_set = new objects_set();
$where_config = array(
     "fields" => array("avatarlink","lastseen","objectmode"),
     "matches" => array("=",">=","IN"),
     "values" => array($slconfig->get_owner_av(),$one_hour_ago,$owner_objects_list),
     "types" => array("i","i","s"),
);
$objects_set->load_with_config($where_config);
$region_set = new region_set();
$region_set->load_ids($objects_set->get_all_by_field("regionlink"));
