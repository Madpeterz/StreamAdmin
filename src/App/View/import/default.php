<?php

$config_areas = array(
    "Setup" => "setup",
    "Servers" => "servers",
    "Packages" => "packages",
    "Avatars" => "avatars",
    "Streams" => "streams",
    "Clients" => "clients",
    "Transactions" => "transactions"
);
$this->output->addSwapTagString("page_title", " Select action");
$table_head = array("Name");
$table_body = [];
$loop = 0;
foreach ($config_areas as $key => $value) {
    $entry = [];
    $entry[] = '<a href="[[url_base]]import/' . $value . '">' . $key . '</a>';
    $table_body[] = $entry;
    $loop++;
}
$this->output->addSwapTagString("page_content", render_table($table_head, $table_body));
