<?php

$check_objects = array("server","template","package","stream","slconfig","textureconfig");
$all_ok = true;
foreach ($check_objects as $check) {
    $obj = new $check();
    if ($obj->HasAny() == false) {
        $all_ok = false;
        $this->output->redirect($check);
        break;
    }
}
if ($all_ok == true) {
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
    if ($session->get_ownerlevel() == 1) {
        $config_areas["R4 import"] = "import";
        $config_areas["Bot"] = "bot";
        $config_areas["Staff"] = "staff";
        $config_areas["Banlist"] = "banlist";
    }
    $this->output->setSwapTagString("html_title", "Config");
    $this->output->setSwapTagString("page_title", "[[page_breadcrumb_icon]] [[page_breadcrumb_text]]");
    $this->output->setSwapTagString("page_actions", "");
    $table_head = array("Name");
    $table_body = [];
    $loop = 0;
    foreach ($config_areas as $key => $value) {
        $entry = [];
        $entry[] = '<a href="[[url_base]]' . $value . '">' . $key . '</a>';
        $table_body[] = $entry;
        $loop++;
    }
    $this->output->addSwapTagString("page_content", render_table($table_head, $table_body));
}
