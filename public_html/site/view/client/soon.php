<?php
$whereconfig = array(
    "fields" => array("expireunixtime","expireunixtime"),
    "values" => array(time()+$unixtime_day,time()),
    "types" => array("i","i"),
    "matches" => array("<=",">"),
);
$template_parts["page_title"] .= " With status: Expires soon";
include("site/view/client/with_status.php");
?>
