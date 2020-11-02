<?php

$view_reply->add_swap_tag_string("page_title", " Unsent notecards");
$table_head = array("id","Rental UID","Avatar name");
$table_body = [];
$notecard_set = new notecard_set();
$notecard_set->loadAll();
$rental_set = new rental_set();
$rental_set->load_ids($notecard_set->get_all_by_field("rentallink"));
$avatar_set = new avatar_set();
$avatar_set->load_ids($rental_set->get_all_by_field("avatarlink"));
foreach ($notecard_set->get_all_ids() as $notecard_id) {
    $notecard = $notecard_set->get_object_by_id($notecard_id);
    $rental = $rental_set->get_object_by_id($notecard->get_rentallink());
    $avatar = $avatar_set->get_object_by_id($rental->get_avatarlink());
    $table_body[] = array($notecard->get_id(),$rental->get_rental_uid(),$avatar->get_avatarname());
}
$view_reply->set_swap_tag_string("page_content", render_datatable($table_head, $table_body));
