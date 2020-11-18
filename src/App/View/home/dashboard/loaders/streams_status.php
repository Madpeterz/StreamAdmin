<?php

use App\StreamSet;

$stream_total_sold = 0;
$stream_total_ready = 0;
$stream_total_needwork = 0;
$stream_set = new StreamSet();
$stream_set->loadAll();
foreach ($stream_set->getAllIds() as $stream_id) {
    $stream = $stream_set->getObjectByID($stream_id);
    $server = $server_set->getObjectByID($stream->getServerlink());
    if ($stream->getRentallink() == null) {
        if ($stream->getNeedwork() == false) {
            $stream_total_ready++;
            $server_loads[$server->getId()]["ready"]++;
        } else {
            $stream_total_needwork++;
            $server_loads[$server->getId()]["needwork"]++;
        }
    } else {
        $stream_total_sold++;
        $server_loads[$server->getId()]["sold"]++;
    }
}
