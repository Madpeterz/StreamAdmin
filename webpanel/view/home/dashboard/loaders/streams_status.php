<?php
$stream_total_sold = 0;
$stream_total_ready = 0;
$stream_total_needwork = 0;
$stream_set = new stream_set();
$stream_set->loadAll();
foreach($stream_set->get_all_ids() as $stream_id)
{
    $stream = $stream_set->get_object_by_id($stream_id);
    $server = $server_set->get_object_by_id($stream->get_serverlink());
    if($stream->get_rentallink() == null)
    {
        if($stream->get_needwork() == false)
        {
            $stream_total_ready++;
            $server_loads[$server->get_id()]["ready"]++;
        }
        else
        {
            $stream_total_needwork++;
            $server_loads[$server->get_id()]["needwork"]++;
        }
    }
    else
    {
        $stream_total_sold++;
        $server_loads[$server->get_id()]["sold"]++;
    }
}
?>
