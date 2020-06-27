<?php
$package = new package();
$server = new server();
if($package->HasAny() == true)
{
    if($server->HasAny() == true)
    {
        $template_parts["html_title"] = "Streams";
        $template_parts["page_actions"] = "<a href='[[url_base]]stream/create'><button type='button' class='btn btn-success'>Create</button></a>";
        $template_parts["page_title"] = "[[page_breadcrumb_icon]] [[page_breadcrumb_text]] / ";
        $check_file = "site/view/stream/".$area.".php";
        if(file_exists($check_file) == true)
        {
            include($check_file);
        }
    }
    else
    {
        redirect("server?message=Please create a server before creating a stream");
    }
}
else
{
    redirect("package?message=Please create a package before creating a stream");
}
?>
