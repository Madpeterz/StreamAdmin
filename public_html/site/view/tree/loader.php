<?php
$stream = new stream();
if($stream->HasAny() == true)
{
    $template_parts["html_title"] = "Tree vender";
    $template_parts["page_actions"] = "<a href='[[url_base]]tree/create'><button type='button' class='btn btn-success'>Create</button></a>";
    $template_parts["page_title"] = "[[page_breadcrumb_icon]] [[page_breadcrumb_text]] / Tree vender";
}
else
{
    redirect("stream");
}
?>
