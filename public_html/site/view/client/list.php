<?php
$rental_set = new rental_set();
$rental_set->loadAll(0,"id","DESC");

$avatar_set = new avatar_set();
$avatar_set->load_ids($rental_set->get_all_by_field("avatarlink"));

$stream_set = new stream_set();
$stream_set->load_ids($rental_set->get_all_by_field("streamlink"));

$package_set = new package_set();
$package_set->load_ids($stream_set->get_all_by_field("packagelink"));

$template_parts["page_title"] .= " [All]";

include("site/view/client/render_list.php");
?>
