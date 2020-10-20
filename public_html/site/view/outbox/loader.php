<?php
$stream = new stream();
if($stream->HasAny() == true)
{
    $template_parts["html_title"] = "Outbox";
    $template_parts["page_actions"] = "";
    $template_parts["page_title"] = "[[page_breadcrumb_icon]] [[page_breadcrumb_text]] / ";
}
else
{
    redirect("stream");
}
?>
