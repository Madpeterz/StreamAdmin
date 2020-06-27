<?php
set_time_limit (120);
ob_start();
include("site/framework/install.php");
if(install_ok() == true)
{
    include("site/view/view.php");
}
else
{
    define("correct",true);
    include("installer/index.php");
}
ob_end_flush();?>
