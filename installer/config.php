<?php
if(defined("correct") == false) {die("Error");}

/*
    please do not edit this unless instructed.
*/
// install mode (Please do not edit this)
function url(){
  return sprintf(
    "%s://%s/",
    isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',
    $_SERVER['SERVER_NAME']
  );
}
$ajax_reply->set_swap_tag_string("site_theme","streamadminr5");
$ajax_reply->set_swap_tag_string("site_lang","en");
$ajax_reply->set_swap_tag_string("html_title_after","StreamAdmin R7");
$ajax_reply->site_name("StreamAdmin R7");
$ajax_reply->set_swap_tag_string("url_base","/");
$ajax_reply->url_base("/");

$view_reply->set_swap_tag_string("site_theme","streamadminr5");
$view_reply->set_swap_tag_string("site_lang","en");
$view_reply->set_swap_tag_string("html_title_after","StreamAdmin R7");
$view_reply->site_name("StreamAdmin R7");
$view_reply->set_swap_tag_string("url_base","/");
$view_reply->url_base("/");
if(getenv('DB_HOST') !== false)
{
    $ajax_reply->set_swap_tag_string("html_title_after",getenv('SITE_TITLE'));
    $ajax_reply->set_swap_tag_string("url_base",getenv('SITE_HOST'));
    $ajax_reply->url_base(getenv('SITE_HOST'));
    $ajax_reply->set_swap_tag_string("site_lang",getenv('SITE_LANG'));

    $view_reply->set_swap_tag_string("html_title_after",getenv('SITE_TITLE'));
    $view_reply->set_swap_tag_string("url_base",getenv('SITE_HOST'));
    $view_reply->url_base(getenv('SITE_HOST'));
    $view_reply->set_swap_tag_string("site_lang",getenv('SITE_LANG'));
}
else
{
    $ajax_reply->set_swap_tag_string("url_base",url());
    $view_reply->set_swap_tag_string("url_base",url());
}
?>
