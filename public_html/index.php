<?php
set_time_limit (120);
if(ob_start() == true)
{
    include("site/framework/install.php");
    if(install_ok() == true)
    {
        include("site/view/view.php");
    }
    else
    {
        define("correct",true);
        include("installer/index.php");
        render();
    }
    ob_end_flush();
}
else
{
    echo "This system requires access to ob_ and its failed";
}
?>
