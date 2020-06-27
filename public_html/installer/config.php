<?php
if(defined("correct") == false) {die("Error");}

/*
    please do not edit this unless instructed.
*/
// install mode (Please do not edit this)
$site_theme = "streamadminr5";
$site_lang = "en";
$template_parts["html_title"] = " Page ";
$template_parts["html_title_after"] = "StreamAdmin";
$template_parts["url_base"] = "/";
if(getenv('DB_HOST') !== false)
{
    $site_theme = "streamadminr5";
    $template_parts["html_title_after"] = getenv('SITE_TITLE');
    $template_parts["url_base"] = getenv('SITE_HOST');
    $site_lang = getenv('SITE_LANG');
}
else
{
    $template_parts["url_base"] = "/";
}
?>
