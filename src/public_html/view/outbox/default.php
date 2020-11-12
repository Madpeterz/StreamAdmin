<?php

$pages = [];
include "webpanel/view/outbox/status.php";
include "webpanel/view/outbox/bulk.package.php";
include "webpanel/view/outbox/bulk.server.php";
include "webpanel/view/outbox/bulk.status.php";


define("swaps_table_paged", true);
include "webpanel/view/shared/swaps_table.php";


$paged_info = new paged_info();
$view_reply->set_swap_tag_string("page_content", $paged_info->render($pages));
