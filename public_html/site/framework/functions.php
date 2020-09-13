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
        if($rental != null ) $api_request->set_rentallink($server->get_id());
        $api_request->set_streamlink($stream->get_id());
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
function render_table(array $table_head, array $table_body,string $classaddon="")
{
    add_vendor("datatable");
    $output = '<table class="'.$classaddon.' table table-striped">';
    $output .= '<thead><tr>';
    foreach($table_head as $entry)
    {
        $output .= '<th scope="col">'.$entry.'</th>';
    }
    $output .= '</tr></thead>';
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
function expired_ago($unixtime=0)
{
    $dif = time()-$unixtime;
    return timeleft_hours_and_days(time()+$dif);
}
function timeleft_hours_and_days($unixtime=0)
{
    $dif = $unixtime-time();
    if($dif > 0)
    {
        $hours = floor((($dif / 60)/60));
        $days = floor($hours / 24);
        if($days > 0)
        {
            $hours -= $days * 24;
        }
        return "".$days." days, ".$hours." hours";
    }
    else return "0 days, 0 hours";
}
function load_template_file($selected_layout="",$layer="",$allow_downgrade=true)
{
    global $site_theme;
    $template_file = "site/theme/".$site_theme."/".$layer."/".$selected_layout.".layout";
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
function redirect($to="",$off_site=false)
{
	if($off_site == true)
	{
		if (!headers_sent()) { header("Location: ".$to.""); }
        else echo "<meta http-equiv=\"refresh\" content=\"0; url=".$to."\">";
	}
	else
	{
		global $template_parts;
        if($template_parts["url_base"] == "") $template_parts["url_base"] = "https://localhost";
		if (!headers_sent()) { header("Location: ".$template_parts["url_base"]."".$to.""); }
        else echo "<meta http-equiv=\"refresh\" content=\"0; url=".$template_parts["url_base"]."/".$to."\">";
	}
}
function clean_and_short_excerpt(string $input,array $allowed_tags=array(),int $max_length)
{
    return clean_excerpt(flame_strip_tags($input,$allowed_tags),$max_length);
}
function clean_excerpt(string $input,int $max_length=200)
{
    if(strlen($input) > $max_length)
    {
        $bits = explode(" ",$input);
        $index = 0;
        $entrys = count($bits);
        $last = "";
        $new_test = "";
        while((strlen($new_test) <= $max_length) && ($index < $entrys))
        {
            $last = $new_test;
            $new_test .= " ".$bits[$index];
            $index++;
        }
        if(strlen($new_test) <= $max_length)
        {
            $last = $new_test;
        }
        return $last;
    }
    else return $input;
}
function flame_strip_tags($html, $allowed_tags=array()) {
  $allowed_tags=array_map("strtolower",$allowed_tags);
  $rhtml=preg_replace_callback('/<\/?([^>\s]+)[^>]*>/i', function ($matches) use (&$allowed_tags) {
    return in_array(strtolower($matches[1]),$allowed_tags)?$matches[0]:'';
  },$html);
  return $rhtml;
}
?>
