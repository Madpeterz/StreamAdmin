<?php
$template_parts["html_title"] = "Staff";
if($session->get_ownerlevel() == true)
{
    $template_parts["page_actions"] = "<a href='[[url_base]]staff/create'><button type='button' class='btn btn-success'>Create</button></a>";
}
else
{
    $template_parts["page_actions"] = "";
}

$template_parts["page_title"] = "[[page_breadcrumb_icon]] [[page_breadcrumb_text]] / Staff ";
?>
