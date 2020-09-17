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
        print "API config loaded";
    }
    else
    {
        print "Unknown API selected";
    }
}
else
{
    print "Invaild API selected";
}
?>
