<?php
$view_reply->set_swap_tag_string("html_title","Banlist");
$view_reply->set_swap_tag_string("page_title","[[page_breadcrumb_icon]] [[page_breadcrumb_text]] / ");
$view_reply->set_swap_tag_string("page_actions","");
$check_file = "site/view/banlist/".$area.".php";
if(file_exists($check_file) == true)
{
    if($session->get_ownerlevel() == 1)
    {
        include($check_file);
    }
    else
    {
        redirect("");
    }
}
?>
