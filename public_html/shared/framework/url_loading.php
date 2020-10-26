<?php
$uri_parts = explode('?', $_SERVER['REQUEST_URI'], 2);
$bits = array_values(array_diff(explode("/",$uri_parts[0]),array("")));
if(count($bits) > 0)
{
	if(strpos($bits[0],"php") !== false)
	{
		array_shift($bits);
	}
}
if(count($bits) == 1)
{
	if($bits[0] == "office")
	{
		$section = "backend";
		$module = "landing";
	}
	else
	{
    	$module = urldecode($bits[0]);
	}
}
else if(count($bits) >= 2)
{
	$shift_bits = 0;
	if($bits[0] == "office")
	{
		$section = "backend";
		$shift_bits = 1;
	}
	if(count($bits) >= (1+$shift_bits)) $module = $bits[0+$shift_bits];
    if(count($bits) >= (2+$shift_bits)) $area = $bits[1+$shift_bits];
	if(count($bits) >= (3+$shift_bits)) $page = $bits[2+$shift_bits];
	if(count($bits) >= (4+$shift_bits)) $optional = $bits[3+$shift_bits];
}
?>
