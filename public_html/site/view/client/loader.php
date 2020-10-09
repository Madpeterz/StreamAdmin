<?php
$stream = new stream();
if($stream->HasAny() == true)
{
    $view_reply->set_swap_tag_string("html_title","Clients");
    $view_reply->set_swap_tag_string("page_title","[[page_breadcrumb_icon]] [[page_breadcrumb_text]] / ");
    $view_reply->set_swap_tag_string("page_actions","<a href='[[url_base]]client/create'><button type='button' class='btn btn-success'>Create</button></a>");
    $check_file = "site/view/client/".$area.".php";
    if(file_exists($check_file) == true)
    {
        include($check_file);
    }
}
else
{
    $view_reply->redirect("stream?message=Please create a stream before creating a client");
}
?>
