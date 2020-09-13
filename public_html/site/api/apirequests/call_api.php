<?php
$stream = new stream();
$soft_fail = false;
$status = false;
$message = "Started call_api";
$current_step = $functionname;
if($stream->load($api_request->get_streamlink()) == true)
{
    $server_api_helper = new serverapi_helper($stream);
    if(method_exists($server_api_helper,$current_step) == true)
    {
        $status = $server_api_helper->$functionname();
        $message = $server_api_helper->get_message();
        if($status == true)
        {
            $remove_status = $api_request->remove_me();
            if($remove_status["status"] == true)
            {
                $why_failed = "";
                if($logic_step != "opt")
                {
                    include("site/api_serverlogic/".$logic_step.".php");
                    $status = $api_serverlogic_reply;
                    if($status == true)
                    {
                        $message = "ok";
                    }
                    else
                    {
                        $message = $why_failed;
                    }
                }
                else
                {
                    $message = "ok";
                }
            }
            else
            {
                $message = "Unable to remove old api request";
            }
        }
    }
    else
    {
        $message = "Unable to api function: ".$functionname;
    }
}
else
{
    $message = "Unable to load stream";
}
?>
