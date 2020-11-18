<?php

$this->output->addSwapTagString("page_title", " In package:");
$package = new package();
$server_set = new server_set();
$server_set->loadAll();
if ($package->load_by_field("package_uid", $page) == true) {
    $this->output->addSwapTagString("page_title", " " . $package->get_name());
    $this->output->addSwapTagString("page_title", " (" . $package->get_package_uid() . ")");
    $stream_set = new stream_set();
    $stream_set->load_on_field("packagelink", $package->getId());

    $rental_set = new rental_set();
    $rental_set->loadIds($stream_set->getAllByField("rentallink"));
    $rental_set_ids = $rental_set->getAllIds();

    include "webpanel/view/stream/render_list.php";
} else {
    $this->output->redirect("stream?messagebubble=Unable to find package&bubbletype=warning");
}
