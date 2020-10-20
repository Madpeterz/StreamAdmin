<?php
$stream = new stream();
if($stream->HasAny() == true)
{
    $template_parts["html_title"] = "Texture packs";
    $template_parts["page_actions"] = "<a href='[[url_base]]textureconfig/create'><button type='button' class='btn btn-success'>Create</button></a>";
    $template_parts["page_title"] = "[[page_breadcrumb_icon]] [[page_breadcrumb_text]] / ";
}
else
{
    redirect("stream");
}
?>
