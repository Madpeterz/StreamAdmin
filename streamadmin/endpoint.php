<?php
ob_start();
include "shared/framework/install.php";
if(defined("installed") == true)
{
    include "endpoints/endpoint/load.php";
}
else
{
    echo "disabled - install mode";
}
ob_end_flush();
?>
