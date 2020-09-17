<?php
include("site/framework/loader_light.php");
include("site/lang/control/".$site_lang.".php");
$redirect = null;
$status = true;
$soft_fail = false;
$reply = array();
if($session->get_logged_in() == true)
{
    if(file_exists("site/control/".$module."/".$area.".php") == true)
    {
        if(file_exists("site/lang/control/".$module."/".$site_lang.".php") == true)
        {
            include("site/lang/control/".$module."/".$site_lang.".php");
        }
        include("site/control/".$module."/".$area.".php");
    }
    else
    {
        $status = false;
        print $lang["ld.error.1"];
    }
}
else
{
    if(file_exists("site/control/login/".$area.".php") == true)
    {
        include("site/lang/control/login/".$site_lang.".php");
        include("site/control/login/".$area.".php");
    }
    else
    {
        $status = false;
        print $lang["ld.error.2"];
    }
}
if($status == false)
{
    if($soft_fail == false)
    {
        $sql->flagError();
    }
}
$buffer = ob_get_contents();
ob_clean();
$reply["status"] = $status;
$reply["message"] = $buffer;
if($redirect != null)
{
    if($redirect == "here") $redirect = "";
    $reply["redirect"] = "".$template_parts["url_base"]."".$redirect."";
}
print json_encode($reply);
?>
