<?php
$table_head = array("Server","Status");
$table_body = array();


foreach($server_set->get_all_ids() as $server_id)
{
    $server = $server_set->get_object_by_id($server_id);
    $entry = array();
    $servername = '<a href="[[url_base]]stream/onserver/'.$server->get_id().'"><strong>'.$server->get_domain().'</strong></a><br/>';
    $servername .= '<h6><span class="badge badge-success">Ready <span class="badge badge-light">'.$server_loads[$server->get_id()]["ready"].'</span></span> ';
    $servername .= '<span class="badge badge-warning">Needwork <span class="badge badge-light">'.$server_loads[$server->get_id()]["needwork"].'</span></span> ';
    $servername .= '<span class="badge badge-info">Sold <span class="badge badge-light">'.$server_loads[$server->get_id()]["sold"].'</span></span></h6>';
    $entry[] = $servername;
    $serverstatus = '<div class="serverstatusdisplay">';
    if($server->get_api_serverstatus() == true)
    {
        $serverstatus .= '<sub data-loading="<div class=\'spinner-border spinner-border-sm text-primary\' role=\'status\'><span class=\'sr-only\'>Loading...</span></div>"
        data-repeatingrate="7000" class="ajaxonpageload" data-loadmethod="post" data-loadurl="[[url_base]]server/server_load/'.$server->get_id().'"></sub>';
    }
    else
    {
        $serverstatus .= '<sub> </sub>';
    }
    $serverstatus .= '</div>';
    $entry[] = $serverstatus;
    $table_body[] = $entry;
}
$sub_grid_servers = new grid();
$sub_grid_servers->add_content('<h4>servers</h4>',12);
$sub_grid_servers->add_content(render_table($table_head,$table_body,"",false),12);
?>
