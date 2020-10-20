<?php
$stream = new stream();
if($stream->HasAny() == true)
{
    $template_parts["html_title"] = "Notices";
    $template_parts["page_actions"] = "<a href='[[url_base]]notice/create'><button type='button' class='btn btn-success'>Create</button></a>";
    $template_parts["page_title"] = "[[page_breadcrumb_icon]] [[page_breadcrumb_text]] / Notices";
    $view_reply->set_swap_tag_string("html_title","R4 import");
    $view_reply->set_swap_tag_string("page_title","Import /");
    $view_reply->set_swap_tag_string("page_actions","");
}
else
{
    redirect("stream");
}
?>
