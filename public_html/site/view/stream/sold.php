<?php
$template_parts["page_title"] .= " With status: Sold";
$server_set = new server_set();
$server_set->loadAll();
$whereconfig = array(
    "fields" => array("rentallink"),
    "values" => array(null),
    "types" => array("s"),
    "matches" => array("IS NOT"),
);
include("site/view/stream/with_status.php");
?>
