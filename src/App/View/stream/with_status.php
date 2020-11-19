<?php

$this->output->setSwapTagString("page_actions", "");
$stream_set = new stream_set();
$stream_set->loadWithConfig($whereconfig);

$rental_set = new rental_set();
$rental_set->loadIds($stream_set->getAllByField("rentallink"));
$rental_set_ids = $rental_set->getAllIds();

include "webpanel/view/stream/render_list.php";
