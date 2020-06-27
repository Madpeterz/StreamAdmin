<?php
$template_parts["html_title"] = "Avatars";
$template_parts["page_actions"] = "<a href='[[url_base]]avatar/create'><button type='button' class='btn btn-success'>Create</button></a>";
$template_parts["page_title"] = "[[page_breadcrumb_icon]] [[page_breadcrumb_text]] / Avatars ";
$check_file = "site/view/avatar/".$area.".php";
if(file_exists($check_file) == true)
{
    include($check_file);
}
?>
