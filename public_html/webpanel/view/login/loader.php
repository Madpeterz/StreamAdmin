<?php
if($area == "reset")
{
    $view_reply->set_swap_tag_string("html_title","Reset password");
    $view_reply->add_swap_tag_string("page_content",file_get_contents("theme/".$site_theme."/blocks/login/reset.layout"));
}
else if($area == "resetwithtoken")
{
    $view_reply->set_swap_tag_string("html_title","Recover password");
    $view_reply->add_swap_tag_string("page_content",file_get_contents("theme/".$site_theme."/blocks/login/passwordrecover.layout"));
}
else if($area == "logout")
{
    $session->end_session();
    $view_reply->redirect("");
}
else
{
    $view_reply->set_swap_tag_string("html_title","Login");
    $view_reply->add_swap_tag_string("why_logged_out",$session->get_why_logged_out());
    $view_reply->add_swap_tag_string("page_content",file_get_contents("theme/".$site_theme."/blocks/login/login.layout"));
}

?>
