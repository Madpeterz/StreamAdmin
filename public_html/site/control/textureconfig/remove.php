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
            echo $lang["textureconfig.rm.info.1"];
        }
        else
        {
            echo sprintf($lang["textureconfig.cr.error.13"],$remove_status["message"]);
        }
    }
    else
    {
        echo $lang["textureconfig.rm.error.2"];
    }
}
else
{
    $redirect ="textureconfig/manage/".$page."";
    echo $lang["textureconfig.rm.error.1"];
}
?>
