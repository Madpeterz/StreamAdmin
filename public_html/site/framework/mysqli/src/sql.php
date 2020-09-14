<?php
$framework_loading = array(
    "core","functions","binds","add","update","remove","select","count","old_binds","custom","shims",
);
foreach($framework_loading as $load_sql_class)
{
    require_once("".$mysqli_load_path."".$load_sql_class.".php");
}
// boot the sql controler
// $sql = new mysqli_controler();

class mysqli_controler extends mysqli_shims
{
    // add any custom stuff here
}
?>
