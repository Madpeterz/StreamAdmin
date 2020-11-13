<?php

$pages = [];
include "webpanel/view/outbox/status.php";
include "webpanel/view/outbox/bulk.package.php";
include "webpanel/view/outbox/bulk.server.php";
include "webpanel/view/outbox/bulk.status.php";


define("swaps_table_paged", true);
include "webpanel/view/shared/swaps_table.php";


$paged_info = new paged_info();
$this->output->setSwapTagString("page_content", $paged_info->render($pages));
