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
    if(file_exists("site/config/site_installed.php") == true)
    {
        include("site/config/site_installed.php");
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
?>
