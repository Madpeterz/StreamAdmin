<?php
$menu_items = array(
    "Dashboard" => array(
        "icon" => "fas fa-home",
        "target" => "",
        "active_on" => array("home"),
    ),
    "Clients" => array(
        "icon" => "fas fa-users",
        "target" => "client",
        "active_on" => array("client"),
    ),
    "Reports" => array(
        "icon" => "fas fa-balance-scale-right",
        "target" => "reports",
        "active_on" => array("reports"),
    ),
    "Outbox" => array(
        "icon" => "fas fa-mail-bulk",
        "target" => "outbox",
        "active_on" => array("outbox"),
    ),
    "Streams" => array(
        "icon" => "fas fa-satellite-dish",
        "target" => "stream",
        "active_on" => array("stream"),
    ),
    "Packages" => array(
        "icon" => "fas fa-box",
        "target" => "package",
        "active_on" => array("package"),
    ),
    "Resellers" => array(
        "icon" => "fas fa-portrait",
        "target" => "reseller",
        "active_on" => array("reseller"),
    ),
    "TreeVend" => array(
        "icon" => "fas fa-list-ul",
        "target" => "tree",
        "active_on" => array("tree"),
    ),
    "Config" => array(
        "icon" => "fas fa-cogs",
        "target" => "config",
        "active_on" => array("banlist","config","template","slconfig","textureconfig","avatar","transactions","staff","notice","objects","server"),
    ),
);

$output = "";
foreach($menu_items as $menu_key => $menu_config)
{
    $output .= '<li class="nav-item">';
    $output .= '<a href="[[url_base]]'.$menu_config["target"].'" class="nav-link';
    if(in_array($module,$menu_config["active_on"]) == true)
    {
        $output .= " active";
        $template_parts["page_breadcrumb_icon"] = '<i class="'.$menu_config["icon"].' text-success"></i>';
        $template_parts["page_breadcrumb_text"] = '<a href="[[url_base]]'.$menu_config["target"].'">'.$menu_key.'</a>';
    }
    $output .= '"><i class="'.$menu_config["icon"].' text-success"></i> '.$menu_key.'</a>';
    $output .= '</li>';
}
$view_reply->set_swap_tag_string("html_menu",$output);
?>
