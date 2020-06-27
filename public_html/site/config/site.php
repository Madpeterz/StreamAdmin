<?php
$site_theme = "streamadminr5";
$site_lang = "en";
$template_parts["html_title"] = " Page ";
$template_parts["html_title_after"] = "[[INSTALL_SITE_NAME]]";
$template_parts["url_base"] = "[[INSTALL_SITE_URI]]";
if(getenv('DB_HOST') !== false)
{
	$site_theme = "streamadminr5";
	$template_parts["html_title_after"] = getenv('SITE_TITLE');
	$template_parts["url_base"] = getenv('SITE_HOST');
	$site_lang = getenv('SITE_LANG');
}
?>
