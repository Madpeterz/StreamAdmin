<?php
$pages = array();
define("swaps_table_paged",true);
include("site/view/outbox/status.php");
include("site/view/outbox/bulk.package.php");
include("site/view/outbox/bulk.server.php");
include("site/view/outbox/bulk.status.php");
include("site/view/shared/swaps_table.php");
$paged_info = new paged_info();
echo $paged_info->render($pages);
?>
