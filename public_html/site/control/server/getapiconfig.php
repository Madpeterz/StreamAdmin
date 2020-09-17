<?php
$input = new inputFilter();
$apilink = $input->postFilter("apilink","integer");
$api = new apis();
$status = false;
if($apilink > 0)
{
    if($api->load($apilink) == true)
    {
        foreach($api->get_fields() as $apifield)
        {
            $getter = "get_".$apifield;
            $reply[$apifield] = $api->$getter();
        }
        $status = true;
        $reply["update_api_flags"] = true;
        echo "API config loaded";
    }
    else
    {
        echo "Unknown API selected";
    }
}
else
{
    echo "Invaild API selected";
}
?>
