<?php
$soft_fail = false;
$timewindow = 120;
include("site/framework/loader_light.php");
include("site/lang/api/".$site_lang.".php");
include("site/api/start_step1.php");
if($all_found == true)
{
    include("site/api/start_step2.php");
}
else
{
    print $lang["ld.error.1"];
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
print json_encode($reply);
?>
