<?php
if(getenv('DB_HOST') !== false)
{
	$site_theme = "streamadminr5";
	$template_parts["html_title_after"] = getenv('SITE_TITLE');
	$template_parts["url_base"] = getenv('SITE_HOST');
	$site_lang = getenv('SITE_LANG');
}
else
{
    if(file_exists("shared/config/site_installed.php") == true)
    {
        include("shared/config/site_installed.php");
    }
	else
	{
		$site_theme = "streamadminr5";
		$site_lang = "en";
		$template_parts["html_title"] = " Page ";
		$template_parts["html_title_after"] = "Streamadmin R7";
		$template_parts["url_base"] = "http://localhost/";
	}
}

$ajax_reply->set_swap_tag_string("site_theme",$site_theme);
$ajax_reply->set_swap_tag_string("site_lang",$site_lang);
$ajax_reply->set_swap_tag_string("html_title_after",$template_parts["html_title_after"]);
$ajax_reply->site_name($template_parts["html_title_after"]);
$ajax_reply->set_swap_tag_string("url_base",$template_parts["url_base"]);
$ajax_reply->url_base($template_parts["url_base"]);

$view_reply->set_swap_tag_string("site_theme",$site_theme);
$view_reply->set_swap_tag_string("site_lang",$site_lang);
$view_reply->set_swap_tag_string("html_title_after",$template_parts["html_title_after"]);
$view_reply->site_name($template_parts["html_title_after"]);
$view_reply->set_swap_tag_string("url_base",$template_parts["url_base"]);
$view_reply->url_base($template_parts["url_base"]);
?>
