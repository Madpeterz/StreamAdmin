<?php

$view_reply->add_swap_tag_string("page_title", " Please select a package");
$package_set = new package_set();
$package_set->loadAll();
$stream_set = new stream_set();
$stream_set->loadAll();

$streams_in_package = [];
foreach ($package_set->get_all_ids() as $package_id) {
    $streams_in_package[$package_id] = array("sold" => 0,"work" => 0,"ready" => 0);
}
foreach ($stream_set->get_all_ids() as $stream_id) {
    $stream = $stream_set->get_object_by_id($stream_id);
    if ($stream->get_rentallink() == null) {
        if ($stream->get_needwork() == false) {
            $streams_in_package[$stream->get_packagelink()]["ready"]++;
        } else {
            $streams_in_package[$stream->get_packagelink()]["work"]++;
        }
    } else {
        $streams_in_package[$stream->get_packagelink()]["sold"]++;
    }
}

$table_head = array("id","Name","Sold","Need work","Ready");
$table_body = [];

foreach ($package_set->get_all_ids() as $package_id) {
    $package = $package_set->get_object_by_id($package_id);
    $entry = [];
    $entry[] = $package->get_id();
    $entry[] = '<a href="[[url_base]]stream/inpackage/' . $package->get_package_uid() . '">' . $package->get_name() . '</a>';
    $entry[] = $streams_in_package[$package_id]["sold"];
    $entry[] = $streams_in_package[$package_id]["work"];
    $entry[] = $streams_in_package[$package_id]["ready"];
    $table_body[] = $entry;
}
$view_reply->set_swap_tag_string("page_content", render_datatable($table_head, $table_body));
