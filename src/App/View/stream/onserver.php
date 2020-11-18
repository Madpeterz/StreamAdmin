<?php

$server_set = new server_set();
$server_set->loadAll();
$server = $server_set->getObjectByID($page);
$this->output->setSwapTagString("page_title", " On server: " . $server->get_domain() . "");
$whereconfig = [
    "fields" => ["serverlink"],
    "values" => [$server->getId()],
    "types" => ["i"],
    "matches" => ["="],
];
include "webpanel/view/stream/with_status.php";
