<?php
$template_parts["page_title"] .= " With status: Need work";
$server_set = new server_set();
$server_set->loadAll();
$whereconfig = array(
    "fields" => array("needwork"),
    "values" => array(1),
    "types" => array("i"),
    "matches" => array("="),
);
include("site/view/stream/with_status.php");
$template_parts["page_actions"] = "<a href='[[url_base]]stream/bulkupdate'><button type='button' class='btn btn-outline-warning btn-sm'>Bulk update</button></a>";
?>
