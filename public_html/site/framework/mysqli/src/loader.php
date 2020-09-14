<?php
if(isset($mysqli_load_path) == false)
{
    $mysqli_load_path = dirname(__FILE__)."/";
}
require_once("".$mysqli_load_path."sql.php");
?>
