<?php
$template_parts["page_title"] = "Dashboard";
$template_parts["page_actions"] = "";
$template_parts["html_title"] = "Dashboard";
$template_parts["page_actions"] = "";

$server_set = new server_set();
$server_set->loadAll();

$server_loads = array();
foreach($server_set->get_all_ids() as $server_id)
{
    $server = $server_set->get_object_by_id($server_id);
    $server_loads[$server_id] = array("ready"=>0,"sold"=>0,"needwork"=>0);
}


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
$notice_set = new notice_set();
$notice_set->loadAll();
$client_expired = 0;
$client_expires_soon = 0;
$client_ok = 0;
$rental = new rental();
$group_count = $sql->group_count($rental->get_table(),"noticelink");
if($group_count["status"] == true)
{
    foreach($group_count["dataset"] as $key => $count)
    {
        $notice = $notice_set->get_object_by_id($key);
        if($notice->get_hoursremaining() == 0) $client_expired++;
        else if($notice->get_hoursremaining() > 24) $client_ok++;
        else $client_expires_soon++;
    }
}

$sub_grid_streams = new grid();
$sub_grid_streams->add_content('<strong>Streams</strong>',12);
$sub_grid_streams->add_content('<h5><a href="[[url_base]]stream/ready"><span class="badge badge-success">Ready <span class="badge badge-light">'.$stream_total_ready.'</span></span></a></h5>',3);
$sub_grid_streams->add_content('<h5><a href="[[url_base]]stream/needwork"><span class="badge badge-warning">Needwork <span class="badge badge-light">'.$stream_total_needwork.'</span></span></a></h5>',3);
$sub_grid_streams->add_content('<h5><a href="[[url_base]]stream/sold"><span class="badge badge-info">Sold <span class="badge badge-light">'.$stream_total_sold.'</span></span></a></h5><br/>',3);

$sub_grid_clients = new grid();
$sub_grid_clients->add_content('<strong>Clients</strong>',12);
$sub_grid_clients->add_content('<h5><a href="[[url_base]]client/expired"><span class="badge badge-danger">Expired <span class="badge badge-light">'.$client_expired.'</span></span></a></h5>',3);
$sub_grid_clients->add_content('<h5><a href="[[url_base]]client/soon"><span class="badge badge-warning">Expires in 24 hours <span class="badge badge-light">'.$client_expires_soon.'</span></span></a></h5>',3);
$sub_grid_clients->add_content('<h5><a href="[[url_base]]client/ok"><span class="badge badge-success">Ok <span class="badge badge-light">'.$client_ok.'</span></span></a></h5><br/>',3);

$table_head = array("Server",
'<h5><span class="badge badge-success">Ready</span></h5>',
'<h5><span class="badge badge-warning">Need work</span></h5>',
'<h5><span class="badge badge-info">Sold</span></h5>');
$table_body = array();


foreach($server_set->get_all_ids() as $server_id)
{
    $server = $server_set->get_object_by_id($server_id);
    $entry = array();
    $entry[] = '<a href="[[url_base]]stream/onserver/'.$server->get_id().'"><strong>'.$server->get_domain().'</strong></a>';
    $entry[] = $server_loads[$server->get_id()]["ready"];
    $entry[] = $server_loads[$server->get_id()]["needwork"];
    $entry[] = $server_loads[$server->get_id()]["sold"];
    $table_body[] = $entry;
}
$sub_grid_servers = new grid();
$sub_grid_servers->add_content('<h4>servers</h4>',12);
$sub_grid_servers->add_content(render_table($table_head,$table_body),12);


$table_head = array("Object type","Count");
$table_body = array();
$objects = new objects();
$one_hour_ago = (time()-$unixtime_hour);
$countdata = $sql->group_count($objects->get_table(),"objectmode",array(array("lastseen"=>">=")),array(array($one_hour_ago=>"i")));
if($countdata["status"] == true)
{
    foreach($countdata["dataset"] as $key => $count)
    {
        $entry = array();
        $entry[] = $key;
        $entry[] = $count;
        $table_body[] = $entry;
    }
}
$sub_grid_objects = new grid();
$sub_grid_objects->add_content('<h4>Objects seen in the last hour</h4>',12);
$sub_grid_objects->add_content(render_table($table_head,$table_body),12);


$main_grid = new grid();
if(file_exists("versions/sql/".$slconfig->get_db_version().".sql") == true)
{
    if($session->get_ownerlevel() == 1)
    {
        $main_grid->add_content("<br/><a href=\"[[url_base]]update\"><button class=\"btn btn-danger btn-block\" type=\"button\">Update now</button></a>",12);
        $main_grid->close_row();
        $main_grid->add_content("Please make sure you have backed up the database before updating!<br/>",12);
    }
    else
    {
        $main_grid->add_content("DB update required <br/> required perm missing",12);
    }
}
if(file_exists("versions/about/".$slconfig->get_db_version().".txt") == true)
{
    $main_grid->close_row();
    $main_grid->add_content("<br/>Version: ".$slconfig->get_db_version()."",12);
    $main_grid->add_content(file_get_contents("versions/about/".$slconfig->get_db_version().".txt"),12);
}
else
{
    $main_grid->add_content("Version: ".$slconfig->get_db_version()."",12);
}
$main_grid->add_content("<br/>",12);
$main_grid->close_row();
$main_grid->add_content($sub_grid_streams->get_output(),6);
$main_grid->add_content($sub_grid_clients->get_output(),6);
$main_grid->add_content("<br/>",12);
$main_grid->close_row();
$main_grid->add_content($sub_grid_servers->get_output(),6);
$main_grid->add_content($sub_grid_objects->get_output(),6);
$main_grid->close_row();
echo $main_grid->get_output();
?>
