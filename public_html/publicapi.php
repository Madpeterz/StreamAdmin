<?php
ob_start();
include("site/framework/install.php");
if(install_ok() == true)
{
    include("site/api_public/loader.php");
}
else
{
    echo "disabled - install mode";
}
ob_end_flush();
?>
