<?php
$template_parts["html_title"] = "Resellers";
$template_parts["page_actions"] = "";
$template_parts["page_title"] = "[[page_breadcrumb_icon]] [[page_breadcrumb_text]] / ";
$check_file = "site/view/reseller/".$area.".php";
if(file_exists($check_file) == true)
{
    include($check_file);
}
?>
