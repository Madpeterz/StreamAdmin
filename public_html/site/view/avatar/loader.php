<?php
$view_reply->set_swap_tag_string("html_title","Avatars");
$view_reply->set_swap_tag_string("page_title","[[page_breadcrumb_icon]] [[page_breadcrumb_text]] / Avatars");
$view_reply->set_swap_tag_string("page_actions","<a href='[[url_base]]avatar/create'><button type='button' class='btn btn-success'>Create</button></a>");
$check_file = "site/view/avatar/".$area.".php";
if(file_exists($check_file) == true)
{
    include($check_file);
}
?>
