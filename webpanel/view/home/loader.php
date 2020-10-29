<?php
$view_reply->set_swap_tag_string("html_title","Dashboard");
$view_reply->set_swap_tag_string("page_title","Dashboard");
$view_reply->set_swap_tag_string("page_actions","");

$dashboard_load_order = array("servers","server_loads","streams_status","notices","objects");
foreach($dashboard_load_order as $load_file)
{
    include "webpanel/view/home/dashboard/loaders/".$load_file.".php";
}

$main_grid = new grid();
$dashboard_display_order = array("streams","clients","servers","objects","versions","final_normal","owner");
foreach($dashboard_display_order as $load_file)
{
    include "webpanel/view/home/dashboard/displays/".$load_file.".php";
}
$view_reply->add_swap_tag_string("page_content",$main_grid->get_output());
?>
