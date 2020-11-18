<?php

use App\Template\Grid;

$table_head = ["Server","Status"];
$table_body = [];


foreach ($server_set->getAllIds() as $server_id) {
    $server = $server_set->getObjectByID($server_id);
    $entry = [];
    $servername = '<a href="[[url_base]]stream/onserver/' . $server->getId() . '"><h5>'
    . $server->get_domain() . '</h5></a>';
    $servername .= '<h6><span class="badge badge-success">Ready <span class="badge badge-light">'
    . $server_loads[$server->getId()]["ready"] . '</span></span> ';
    $servername .= '<span class="badge badge-warning">Needwork <span class="badge badge-light">'
    . $server_loads[$server->getId()]["needwork"] . '</span></span> ';
    $servername .= '<span class="badge badge-info">Sold <span class="badge badge-light">'
    . $server_loads[$server->getId()]["sold"] . '</span></span></h6>';
    $entry[] = $servername;
    $serverstatus = '<div class="serverstatusdisplay">';
    if ($server->get_api_serverstatus() == true) {
        $serverstatus .= '<div data-loading="<div class=\'spinner-border spinner-border-sm '
        . 'text-primary\' role=\'status\'>'
        . '<span class=\'sr-only\'>Loading...</span></div>" data-repeatingrate="7000" class="ajaxonpageload" '
        . 'data-loadmethod="post" data-loadurl="[[url_base]]server/server_load/'
        . $server->getId() . '"></div>';
    } else {
        $serverstatus .= '<sub> </sub>';
    }
    $serverstatus .= '</div>';
    $entry[] = $serverstatus;
    $table_body[] = $entry;
}
$sub_grid_servers = new Grid();
$sub_grid_servers->addContent('<h4>servers</h4>', 12);
$sub_grid_servers->addContent(render_table($table_head, $table_body, "", false), 12);
