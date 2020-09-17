<?php
$status = false;
$rental = new rental();
if($rental->load_by_field("rental_uid",$page) == true)
{
    $stream = new stream();
    if($stream->load($rental->get_streamlink()) == true)
    {
        $server_api_helper = new serverapi_helper($stream);
        $functionname = "api_".$optional."";
        if(method_exists($server_api_helper,$functionname) == true)
        {
            $status = $server_api_helper->$functionname();
            if(is_string($server_api_helper->get_message()) == true)
            {
                if($status == true) echo sprintf($lang["client.api.passed"],$server_api_helper->get_message());
                else echo sprintf($lang["client.api.failed"],$server_api_helper->get_message());
            }
            else
            {
                if($status == true) echo sprintf($lang["client.api.passed"],"No message from api helper");
                else echo sprintf($lang["client.api.failed"],"No message from api helper");
            }
        }
        else
        {
            echo "Unable to load api: ".$functionname;
        }
    }
    else
    {
        echo $lang["client.api.error.2"];
    }
}
else
{
    echo $lang["client.api.error.1"];
}
?>
