<?php
set_time_limit(15);
include("shared/framework/install.php");
if(install_ok() == true)
{
    if($_SERVER["REQUEST_METHOD"] == "POST") include("webpanel/control/loader.php");
    else include("webpanel/view/view.php");
}
else
{
    define("correct",true);
    include("installer/index.php");
}
?>
