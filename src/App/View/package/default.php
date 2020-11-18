<?php

$this->output->addSwapTagString("page_title", " All");
$package_set = new package_set();
$package_set->loadAll();

$table_head = ["id","UID","Name","Listeners","Days","Kbps","Cost"];
$table_body = [];

foreach ($package_set->getAllIds() as $package_id) {
    $package = $package_set->getObjectByID($package_id);
    $entry = [];
    $entry[] = $package->getId();
    $entry[] = '<a href="[[url_base]]package/manage/' . $package->get_package_uid() . '">' . $package->get_package_uid() . '</a>';
    $entry[] = $package->get_name();
    $entry[] = $package->get_listeners();
    $entry[] = $package->get_days();
    $entry[] = $package->get_bitrate();
    $entry[] = $package->get_cost();
    $table_body[] = $entry;
}
$this->output->setSwapTagString("page_content", render_datatable($table_head, $table_body));
