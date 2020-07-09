<?php
$template_parts["html_title"] = "Transactions";
$template_parts["page_actions"] = "";
$template_parts["page_title"] = "[[page_breadcrumb_icon]] [[page_breadcrumb_text]] / Transactions: ";
$check_file = "site/view/transactions/".$area.".php";
$month = date("m");
$year = date("Y");
if(file_exists($check_file) == true)
{
    include($check_file);
}
?>
