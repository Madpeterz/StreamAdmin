<?php
function create_pending_api_request(server $server,stream $stream,?rental $rental,string $eventname,string $errormessage="error: %1\$s %2\$s",bool $save_to_why_failed=false) : bool
{
    global $why_failed, $no_api_action;
    if($eventname == "core_send_details")
    {
        $detail = new detail();
        $detail->set_rentallink($rental->get_id());
        $create_status = $detail->create_entry();
        $status = $create_status["status"];
        if($status == false)
        {
            $why_failed = $errormessage;
        }
        return $status;
    }
    else
    {
        $no_api_action = false;
        $api_request = new api_requests();
        $api_request->set_serverlink($server->get_id());
        if($rental != null) $api_request->set_rentallink($rental->get_id());
        $api_request->set_streamlink($stream->get_id());
        $api_request->set_eventname($eventname);
        $api_request->set_message("in Q");
        $api_request->set_last_attempt(time());
        $reply = $api_request->create_entry();
        if($reply["status"] == false)
        {
            if($save_to_why_failed == true)
            {
                $why_failed = sprintf($errormessage,$eventname,$reply["message"]);
            }
            else
            {
                echo sprintf($errormessage,$eventname,$reply["message"]);
            }
        }
        return $reply["status"];
    }
}
function render_table(array $table_head, array $table_body,string $classaddon="",bool $show_head=true)
{
    add_vendor("datatable");
    $output = '<table class="'.$classaddon.' table table-striped">';
    if($show_head == true)
    {
        $output .= '<thead><tr>';
        foreach($table_head as $entry)
        {
            $output .= '<th scope="col">'.$entry.'</th>';
        }
        $output .= '</tr></thead>';
    }

    $output .= '<tbody>';
    foreach($table_body as $row)
    {
        $output .= "<tr>";
        foreach($row as $entry)
        {
            $output .= "<td>".$entry."</td>";
        }
        $output .= "</tr>";
    }
    $output .= '</tbody>';
    $output .= '</table>';
    return $output;
}
function render_datatable(array $table_head, array $table_body)
{
    add_vendor("datatable");
    return render_table($table_head, $table_body,"datatable-default display responsive");
}
function expired_ago($unixtime=0,bool $use_secs=false)
{
    $dif = time()-$unixtime;
    return timeleft_hours_and_days(time()+$dif,$use_secs);
}
function timeleft_hours_and_days($unixtime=0,bool $use_secs=false)
{
    $dif = $unixtime-time();
    if($dif > 0)
    {
        $mins = floor(($dif / 60));
        $hours = floor(($mins/60));
        $days = floor($hours / 24);
        if($days > 0)
        {
            $hours -= $days * 24;
            return $days." days, ".$hours." hours";
        }
        else
        {
            if(($use_secs == false) || ($hours > 0))
            {
                $mins -= $hours * 60;
                return $hours." hours, ".$mins." mins";
            }
            else
            {
                if($use_secs == false)
                {
                    return $mins." mins";
                }
                else
                {
                    $dif -= $mins * 60;
                    if($mins > 0)
                    {
                        return $mins." mins, ".$dif." secs";
                    }
                    else
                    {
                        return $dif." secs";
                    }
                }
            }
        }

    }
    else return "0 days, 0 hours";
}
function load_template_file($selected_layout="",$layer="",$allow_downgrade=true)
{
    global $site_theme;
    $template_file = "theme/".$site_theme."/".$layer."/".$selected_layout.".layout";
    if(file_exists($template_file))
    {
        require_once($template_file);
    }
    else
    {
        if($allow_downgrade == true)
        {
            if($layer == "layout") load_template_file($selected_layout,"shared");
            else if($layer != "shared") load_template_file($selected_layout,"layout");
            else echo "Unable to find template file ".$selected_layout."<Br/>";
        }
        else echo "Unable to find template file ".$selected_layout." no downgrade allowed<Br/>";
    }
}
function is_checked(bool $input_value) : string
{
    if($input_value == true) return " checked ";
    else return "";
}
function load_template($selected_layout="",$allow_downgrade=true)
{
    global $section;
    load_template_file($selected_layout,$section,$allow_downgrade);
}
?>
