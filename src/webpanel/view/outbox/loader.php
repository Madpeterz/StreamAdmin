<?php
$stream = new stream();
if($stream->HasAny() == true)
{
    $view_reply->set_swap_tag_string("html_title","Outbox");
    $view_reply->set_swap_tag_string("page_title","[[page_breadcrumb_icon]] [[page_breadcrumb_text]] / ");
    $view_reply->set_swap_tag_string("page_actions","");
}
else
{
    $view_reply->redirect("stream?message=Please create a stream first");
}
?>
