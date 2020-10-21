<?php
$table_head = array("Server",
'<h5><span class="badge badge-success">Ready</span></h5>',
'<h5><span class="badge badge-warning">Need work</span></h5>',
'<h5><span class="badge badge-info">Sold</span></h5>');
$table_body = array();


foreach($server_set->get_all_ids() as $server_id)
{
    $server = $server_set->get_object_by_id($server_id);
    $entry = array();
    $serverstatus = '<a href="[[url_base]]stream/onserver/'.$server->get_id().'"><strong>'.$server->get_domain().'</strong></a><br/><br/><div class="serverstatusdisplay">';
    if($server->get_api_serverstatus() == true)
    {
        $serverstatus .= '<sub data-loading="
        CPU: <span class=\'text-light\'>?</span> |
        Ram: <span class=\'text-light\'>?</span> % |
        Str: <span class=\'text-light\'>?</span> %"
        data-repeatingrate="7000" class="ajaxonpageload" data-loadurl="[[url_base]]ajax.php/server/server_load/'.$server->get_id().'"></sub>';
    }
    else
    {
        $serverstatus .= '<sub> </sub>';
    }
    $serverstatus .= '</div>';
    $entry[] = $serverstatus;
    $entry[] = $server_loads[$server->get_id()]["ready"];
    $entry[] = $server_loads[$server->get_id()]["needwork"];
    $entry[] = $server_loads[$server->get_id()]["sold"];
    $table_body[] = $entry;
}
$sub_grid_servers = new grid();
$sub_grid_servers->add_content('<h4>servers</h4>',12);
$sub_grid_servers->add_content(render_table($table_head,$table_body),12);
?>
