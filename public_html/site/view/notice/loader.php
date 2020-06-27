<?php
$stream = new stream();
if($stream->HasAny() == true)
{
    $template_parts["html_title"] = "Notices";
    $template_parts["page_actions"] = "<a href='[[url_base]]notice/create'><button type='button' class='btn btn-success'>Create</button></a>";
    $template_parts["page_title"] = "[[page_breadcrumb_icon]] [[page_breadcrumb_text]] / Notices";
    $check_file = "site/view/notice/".$area.".php";
    if(file_exists($check_file) == true)
    {
        include($check_file);
    }
}
else
{
    redirect("stream");
}
?>
