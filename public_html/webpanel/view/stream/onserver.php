<?php
$server_set = new server_set();
$server_set->loadAll();
$server = $server_set->get_object_by_id($page);
$view_reply->set_swap_tag_string("page_title"," On server: ".$server->get_domain()."");
$whereconfig = array(
    "fields" => array("serverlink"),
    "values" => array($server->get_id()),
    "types" => array("i"),
    "matches" => array("="),
);
include("webpanel/view/stream/with_status.php");
?>
