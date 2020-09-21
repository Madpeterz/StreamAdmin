<?php
// Please do not edit any values below this line
// ===========================================================================================
$section = "public";
$module = "home";
$area = "";
$optional = "";
$page = 0;
$load_id = 0;
$unixtime_min = 60;
$unixtime_hour = $unixtime_min * 60;
$unixtime_day = $unixtime_hour * 24;
$unixtime_week = $unixtime_day * 7;
$yearandhalf_unixtime = (($unixtime_day*31)*18);
$jquery_addon_script = "";
$debug_text = "";
$force_module = "";
$force_area = "";
$template_parts = array(
    "url_base" => "", // set this in config/load
    "html_title" => "",
    "html_title_after" => "", // set this in config/load
    "html_cs_top" => "",
    "html_menu" => "",

    "page_title" => "Not setup :(",
    "page_content" => "Not setup :(",
    "page_actions" => "Not setup :(",

    "page_breadcrumb_icon" => "",
    "page_breadcrumb_text" => "",

    "html_js_bottom" => "<script>var url_base = '[[url_base]]';</script> ",
    "html_js_onready" => " ",
    "LOGIN_LOGOUT" => "<a href=\"[[url_base]]members\">Login</a>",
    "html_header_layout" => "",
    "username" => "",
);
$page_buffer = "";
$timezone_name = "Europe / London";
date_default_timezone_set("Europe/London");

?>
