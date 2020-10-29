<?php
if(defined("correct") == true)
{
    include "shared/framework/load.php";
    add_vendor("website");
    include "theme/streamadminr5/layout/install/template.php";
    $input = new inputFilter();
    if($module == "owner")
    {
        include "installer/owner.php";
    }
    else if($module == "test")
    {
        include "installer/test.php";
    }
    else if($module == "install")
    {
        include "installer/install.php";
    }
    else if($module == "setup")
    {
        include "installer/setup.php";
    }
    else if($module == "final")
    {
        include "installer/final.php";
    }
    else
    {
        include "installer/dbconfig.php";
    }
    $view_reply->render_page();
}
else
{
    die("Please do not attempt to run installer directly it will break something!");
}
?>
