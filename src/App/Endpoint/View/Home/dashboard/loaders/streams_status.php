<?php

use App\Models\StreamSet;

$stream_total_sold = 0;
$stream_total_ready = 0;
$stream_total_needWork = 0;
$stream_set = new StreamSet();
$stream_set->loadAll();
foreach ($stream_set->getAllIds() as $stream_id) {
    $stream = $stream_set->getObjectByID($stream_id);
    $server = $server_set->getObjectByID($stream->getServerLink());
    if ($stream->getRentalLink() == null) {
        if ($stream->getNeedWork() == false) {
            $stream_total_ready++;
            $server_loads[$server->getId()]["ready"]++;
        } else {
            $stream_total_needWork++;
            $server_loads[$server->getId()]["needWork"]++;
        }
    } else {
        $stream_total_sold++;
        $server_loads[$server->getId()]["sold"]++;
    }
}
