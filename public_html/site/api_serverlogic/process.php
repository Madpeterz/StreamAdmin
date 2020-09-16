<?php
$api_logiclang = array(
    "failed.create" => "Unable to create event",
    "failed.noapi" => "Unable to find API config",
    "failed.noserver" => "Unable to find server"
);
$api_serverlogic_reply = true;
if(isset($site_lang) == false)
{
    error_log(print_r(debug_backtrace(), true));
    $site_lang = "en";
}
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
            $exit = false;
            while($exit == false)
            {
                if(array_key_exists($current_step,$steps) == true)
                {
                    $current_step = $steps[$current_step];
                }
                else
                {
                    $current_step = "none";
                }
                if($current_step != "none")
                {
                    $has_api_step = true;
                    if($current_step != "core_send_details")
                    {
                        $has_api_step = false;
                        $get_name = "get_".$current_step."";
                        if(($api->$get_name() == 1) && ($server->$get_name() == 1))
                        {
                            $has_api_step = true;
                        }
                    }
                    if($has_api_step == true)
                    {
                        $all_ok = true;
                        $exit = true;
                        if($current_step == "core_send_details")
                        {
                            if($rental == null)
                            {
                                $rental = new rental();
                                $all_ok = $rental->load_by_field("streamlink",$stream->get_id());
                            }
                        }
                        if($all_ok == true)
                        {
                            $no_api_action = false;
                            $api_serverlogic_reply = create_pending_api_request($server,$stream,$rental,$current_step,$api_logiclang["failed.create"],true);
                        }
                        else
                        {
                            $api_serverlogic_reply = false;
                        }
                    }
                }
                else
                {
                    $exit = true;
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
