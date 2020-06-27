<?php
$check_objects = array("server","template","package","stream","slconfig","textureconfig");
$all_ok = true;
foreach($check_objects as $check)
{
    $obj = new $check();
    if($obj->HasAny() == false)
    {
        $all_ok = false;
        redirect($check);
        break;
    }
}
if($all_ok == true)
{
    $config_areas = array(
        "Avatars" => "avatar",
        "Template" => "template",
        "System config" => "slconfig",
        "Textures" => "textureconfig",
        "Transactions" => "transactions",
        "Notices" => "notice",
        "Objects" => "objects",
        "Servers" => "server",
    );
    if($session->get_ownerlevel() == 1)
    {
        $config_areas["R4 import"] = "import";
        $config_areas["Bot"] = "bot";
        $config_areas["Staff"] = "staff";
    }
    $template_parts["page_actions"] = "";
    $template_parts["html_title"] = "Config";
    $template_parts["page_title"] = "[[page_breadcrumb_icon]] [[page_breadcrumb_text]]";
    $table_head = array("Name");
    $table_body = array();
    $loop = 0;
    foreach($config_areas as $key => $value)
    {
        $entry = array();
        $entry[] = '<a href="[[url_base]]'.$value.'">'.$key.'</a>';
        $table_body[] = $entry;
        $loop++;
    }
    echo render_table($table_head,$table_body);
}
?>
