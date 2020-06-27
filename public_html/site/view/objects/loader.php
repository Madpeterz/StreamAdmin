<?php
$template_parts["html_title"] = "Objects";
$template_parts["page_actions"] = "<a href='[[url_base]]objects/clear'><button type='button' class='btn btn-outline-warning'>Clear</button></a>";
$template_parts["page_title"] = "[[page_breadcrumb_icon]] [[page_breadcrumb_text]] / Objects";
$check_file = "site/view/objects/".$area.".php";
if(file_exists($check_file) == true)
{
    include($check_file);
}
?>
