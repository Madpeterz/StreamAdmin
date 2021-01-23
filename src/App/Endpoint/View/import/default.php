<?php

$config_areas = [
    "Setup" => "setup",
    "Servers" => "servers",
    "Packages" => "packages",
    "Avatars" => "avatars",
    "Streams" => "streams",
    "Clients" => "clients",
    "Transactions" => "transactions",
];
$this->output->addSwapTagString("page_title", " Select action");
$table_head = ["Name"];
$table_body = [];
$loop = 0;
foreach ($config_areas as $key => $value) {
    $entry = [];
    $entry[] = '<a href="[[url_base]]import/' . $value . '">' . $key . '</a>';
    $table_body[] = $entry;
    $loop++;
}
$this->output->addSwapTagString("page_content", $this->renderTable($table_head, $table_body));
