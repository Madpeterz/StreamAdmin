<?php
$required_sl = array("method","action","mode","ownerkey","ownername");
$required_sl_values = array();
$input = new inputFilter();
$all_found = true;
$status = true;
$staticpart = "";
foreach($required_sl as $slvalue)
{
    $value = $input->postFilter($slvalue);
    if($value !== null)
    {
        $required_sl_values[$slvalue] = $value;
        $staticpart .= $value;
    }
    else
    {
        $all_found = false;
    }
}
$hash = $input->postFilter("hash");
if($hash === null)
{
    $all_found = false;
}
$sentunixtime = $input->postFilter("unixtime");
if($sentunixtime === null)
{
    $all_found = false;
}
$ownerhash = $input->postFilter("ownerhash");
if($ownerhash === null)
{
    $all_found = false;
}
?>
