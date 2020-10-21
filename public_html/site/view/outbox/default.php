<?php
$pages = array();
include("site/view/outbox/status.php");
include("site/view/outbox/bulk.package.php");
include("site/view/outbox/bulk.server.php");
include("site/view/outbox/bulk.status.php");


define("swaps_table_paged",true);
include("site/view/shared/swaps_table.php");


$paged_info = new paged_info();
$view_reply->set_swap_tag_string("page_content",$paged_info->render($pages));
?>
