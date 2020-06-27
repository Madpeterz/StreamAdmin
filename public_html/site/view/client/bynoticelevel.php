<?php
$package_set = new package_set();
$package_set->loadAll();
$rental_set = new rental_set();
$rental_set->load_on_field("noticelink",$page);
$avatar_set = new avatar_set();
$avatar_set->load_ids($rental_set->get_all_by_field("avatarlink"));
$stream_set = new stream_set();
$stream_set->load_ids($rental_set->get_all_by_field("streamlink"));
$notice = new notice();
$notice->load($page);
$template_parts["page_title"] .= " By notice level: ".$notice->get_name()."";

include("site/view/client/render_list.php");
?>
