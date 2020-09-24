<?php
ob_start();
include("site/framework/install.php");
if(install_ok() == true)
{
    include("site/endpoint/load.php");
}
else
{
    echo "disabled - install mode";
}
ob_end_flush();
?>
