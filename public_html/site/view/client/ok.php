<?php
$whereconfig = array(
    "fields" => array("expireunixtime"),
    "values" => array(time()+$unixtime_day),
    "types" => array("i"),
    "matches" => array(">"),
);
$template_parts["page_title"] .= " With status: ok";
include("site/view/client/with_status.php");
?>
