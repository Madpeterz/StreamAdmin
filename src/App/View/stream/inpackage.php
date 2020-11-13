<?php

$this->output->addSwapTagString("page_title", " In package:");
$package = new package();
$server_set = new server_set();
$server_set->loadAll();
if ($package->load_by_field("package_uid", $page) == true) {
    $this->output->addSwapTagString("page_title", " " . $package->get_name());
    $this->output->addSwapTagString("page_title", " (" . $package->get_package_uid() . ")");
    $stream_set = new stream_set();
    $stream_set->load_on_field("packagelink", $package->get_id());

    $rental_set = new rental_set();
    $rental_set->load_ids($stream_set->get_all_by_field("rentallink"));
    $rental_set_ids = $rental_set->get_all_ids();

    include "webpanel/view/stream/render_list.php";
} else {
    $this->output->redirect("stream?messagebubble=Unable to find package&bubbletype=warning");
}
