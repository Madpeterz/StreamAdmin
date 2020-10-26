<?php
$view_reply->set_swap_tag_string("page_actions","");
$rental_set = new rental_set();
$rental_set->load_with_config($whereconfig);
$avatar_set = new avatar_set();
$avatar_set->load_ids($rental_set->get_all_by_field("avatarlink"));
$stream_set = new stream_set();
$stream_set->load_ids($rental_set->get_all_by_field("streamlink"));

include("webpanel/view/client/render_list.php");
?>
