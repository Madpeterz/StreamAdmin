<?php
if(isset($mysqli_load_path) == false)
{
    $mysqli_load_path = dirname(__FILE__)."/";
}
include($mysqli_load_path."core.php");
include($mysqli_load_path."functions.php");
include($mysqli_load_path."binds.php");
include($mysqli_load_path."add.php");
include($mysqli_load_path."update.php");
include($mysqli_load_path."remove.php");
include($mysqli_load_path."select.php");
include($mysqli_load_path."count.php");
include($mysqli_load_path."old_binds.php");
include($mysqli_load_path."custom.php");
include($mysqli_load_path."shims.php");

class mysqli_controler extends mysqli_shims
{
    // add any custom stuff here
}
?>
