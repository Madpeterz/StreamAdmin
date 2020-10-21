<?php
$view_reply->add_swap_tag_string("page_title"," Unsent details");
$table_head = array("id","Rental UID","Avatar name");
$table_body = array();
$detail_set = new detail_set();
$detail_set->loadAll();
$rental_set = new rental_set();
$rental_set->load_ids($detail_set->get_all_by_field("rentallink"));
$avatar_set = new avatar_set();
$avatar_set->load_ids($rental_set->get_all_by_field("avatarlink"));
foreach($detail_set->get_all_ids() as $detail_id)
{
    $detail = $detail_set->get_object_by_id($detail_id);
    $rental = $rental_set->get_object_by_id($detail->get_rentallink());
    $avatar = $avatar_set->get_object_by_id($rental->get_avatarlink());
    $table_body[] = array($detail->get_id(),$rental->get_rental_uid(),$avatar->get_avatarname());
}
$view_reply->set_swap_tag_string("page_content",render_datatable($table_head,$table_body));
?>
