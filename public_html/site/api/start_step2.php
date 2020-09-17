<?php
$now = time();
$outofscope = false;
if($sentunixtime > $now)
{
    if($sentunixtime > ($now+$timewindow))
    {
        $outofscope = true;
    }
}
else if($sentunixtime < $now)
{
    if($sentunixtime < ($now-$timewindow))
    {
        $outofscope = true;
    }
}
if($outofscope == false)
{
    include("site/api/start_step3.php");
}
else
{
    print $lang["start_step2_scope"];
}
?>
