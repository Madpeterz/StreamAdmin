<?php
$whereconfig = array(
    "fields" => array("expireunixtime"),
    "values" => array(time()),
    "types" => array("i"),
    "matches" => array("<="),
);
$view_reply->add_swap_tag_string("page_title","With status: Expired");
include("site/view/client/with_status.php");
$view_reply->set_swap_tag_string("page_actions","<a href='[[url_base]]client/bulkremove'><button type='button' class='btn btn-outline-danger btn-sm'>Bulk remove</button></a>");
?>
