<?php
$api_logiclang = array(
    "failed.create" => "Unable to create event",
    "failed.noapi" => "Unable to find API config",
    "failed.noserver" => "Unable to find server"
);
$api_serverlogic_reply = true;
$lang_file = "site/lang/api_serverlogic/".$site_lang.".php";
if(file_exists($lang_file) == true) { include($lang_file); }
if(isset($server) == false)
{
    $server = new server();
    $server->load($stream->get_serverlink());
}
if(isset($no_api_action) == false) { $no_api_action = true; }
if(isset($rental) == false) { $rental = null; }
if(isset($why_failed) == false) { $why_failed = ""; }
if(isset($current_step) == false) { $current_step = ""; }
if($server->is_loaded() == true)
{
    $api = new apis();
    if($api->load($server->get_apilink()) == true)
    {
        if($api->get_id() != 1)
        {
            $cleaned_steps = array();
            if($current_step != "")
            {
                $skip = true;
                foreach($steps as $key => $value)
                {
                    if(($key == $current_step) && ($skip == true))
                    {
                        $skip = false;
                    }
                    if($skip == false)
                    {
                        $cleaned_steps[] = $value;
                    }
                }
            }
            else
            {
                $cleaned_steps = array_values($steps);
            }
            $continue_steps = false;
            foreach($cleaned_steps as $value)
            {
                $has_api_step = false;
                if($value != "core_send_details")
                {
                    $get_name = "get_".$value."";
                    if(($api->$get_name() == 1) && ($server->$get_name() == 1))
                    {
                        $has_api_step = true;
                    }
                }
                else
                {
                    $has_api_step = true;
                }
                if($has_api_step == true)
                {
                    $no_api_action = false;
                    $api_serverlogic_reply = create_pending_api_request($server,$stream,$rental,$value,$api_logiclang["failed.create"],true);
                    break;
                }
            }
        }
    }
    else
    {
        $all_ok = false;
        echo $api_logiclang["failed.noapi"];
    }
}
else
{
    $all_ok = false;
    echo $lang["failed.noserver"];
}
?>
