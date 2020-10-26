<?php
$soft_fail = false;
$timewindow = 120;
include("shared/framework/loader_light.php");
include("shared/lang/api_public/".$site_lang.".php");
include("endpoints/api_public/start_step1.php");
$status = false;
if($all_found == true)
{
    include("endpoints/api_public/start_step2.php");
}
else
{
    echo $lang["ld.error.1"];
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
if($status == true) $status = 1;
else $status = 0;
$reply["status"] = $status;
$reply["message"] = $buffer;
echo json_encode($reply);
?>
