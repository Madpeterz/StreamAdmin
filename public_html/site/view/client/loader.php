<?php
$stream = new stream();
if($stream->HasAny() == true)
{
    $template_parts["html_title"] = "Clients";
    $template_parts["page_actions"] = "<a href='[[url_base]]client/create'><button type='button' class='btn btn-success'>Create</button></a>";
    $template_parts["page_title"] = "[[page_breadcrumb_icon]] [[page_breadcrumb_text]] / ";
    $check_file = "site/view/client/".$area.".php";
    if(file_exists($check_file) == true)
    {
        include($check_file);
    }
}
else
{
    redirect("stream?message=Please create a stream before creating a client");
}
?>
