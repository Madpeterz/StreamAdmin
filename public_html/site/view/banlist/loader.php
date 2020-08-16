<?php
$template_parts["html_title"] = "Banlist";
$template_parts["page_actions"] = "";
$template_parts["page_title"] = "[[page_breadcrumb_icon]] [[page_breadcrumb_text]] / ";
$check_file = "site/view/banlist/".$area.".php";
if(file_exists($check_file) == true)
{
    if($session->get_ownerlevel() == 1)
    {
        include($check_file);
    }
    else
    {
        redirect("");
    }
}
?>
