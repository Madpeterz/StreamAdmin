<?php

$server_loads = [];
foreach ($server_set->getAllIds() as $server_id) {
    $server = $server_set->getObjectByID($server_id);
    $server_loads[$server_id] = ["ready" => 0,"sold" => 0,"needWork" => 0];
}
