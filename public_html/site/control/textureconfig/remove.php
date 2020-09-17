<?php
$input = new inputFilter();
$accept = $input->postFilter("accept");
$redirect="textureconfig";
$status = false;
if($accept == "Accept")
{
    $textureconfig = new textureconfig();
    if($textureconfig->load($page) == true)
    {
        $remove_status = $textureconfig->remove_me();
        if($remove_status["status"] == true)
        {
            $status = true;
            print $lang["textureconfig.rm.info.1"];
        }
        else
        {
            print sprintf($lang["textureconfig.cr.error.13"],$remove_status["message"]);
        }
    }
    else
    {
        print $lang["textureconfig.rm.error.2"];
    }
}
else
{
    $redirect ="textureconfig/manage/".$page."";
    print $lang["textureconfig.rm.error.1"];
}
?>
