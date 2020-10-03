<?php
set_time_limit(15);
include("site/framework/install.php");
if(install_ok() == true)
{
    if($_SERVER["REQUEST_METHOD"] == "POST") include("site/control/loader.php");
    else include("site/view/view.php");
}
else
{
    define("correct",true);
    include("installer/index.php");
    render();
}
?>
