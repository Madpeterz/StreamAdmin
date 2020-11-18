<?php

$this->output->addSwapTagString("page_title", " Texture packs");
$table_head = ["id","ID","name"];
$table_body = [];
$textureconfig_set = new textureconfig_set();
$textureconfig_set->loadAll();

foreach ($textureconfig_set->getAllIds() as $textureconfig_id) {
    $textureconfig = $textureconfig_set->getObjectByID($textureconfig_id);
    $entry = [];
    $entry[] = $textureconfig->getId();
    $entry[] = $textureconfig->getId();
    $entry[] = '<a href="[[url_base]]textureconfig/manage/' . $textureconfig->getId() . '">' . $textureconfig->get_name() . '</a>';
    $table_body[] = $entry;
}
$this->output->setSwapTagString("page_content", render_datatable($table_head, $table_body));
