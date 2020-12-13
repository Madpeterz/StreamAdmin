<?php

use App\Models\Objects;
use App\Models\ObjectsSet;
use App\Models\RegionSet;

$objects = new Objects();
$owner_objects_list = [
    "apirequests",
    "mailserver",
    "noticeserver",
    "detailsserver",
    "notecardsserver",
];
$one_hour_ago = (time() - $unixtime_hour);
$objects_set = new ObjectsSet();
$where_config = [
     "fields" => ["avatarlink","lastseen","objectmode"],
     "matches" => ["=",">=","IN"],
     "values" => [$slconfig->getOwner_av(),$one_hour_ago,$owner_objects_list],
     "types" => ["i","i","s"],
];
$objects_set->loadWithConfig($where_config);
$region_set = new RegionSet();
$region_set->loadIds($objects_set->getAllByField("regionlink"));
