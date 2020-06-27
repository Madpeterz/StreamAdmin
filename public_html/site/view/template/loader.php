<?php
$template_parts["html_title"] = "Templates";
$template_parts["page_actions"] = "<a href='[[url_base]]template/create'><button type='button' class='btn btn-success'>Create</button></a>";
$template_parts["page_title"] = "[[page_breadcrumb_icon]] [[page_breadcrumb_text]] / Teamplate: ";
$check_file = "site/view/template/".$area.".php";
if(file_exists($check_file) == true)
{
    include($check_file);
}
?>
