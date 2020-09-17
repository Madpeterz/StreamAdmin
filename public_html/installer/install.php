<?php
if(defined("correct") == false) {die("Error");}
include("site/config/db.php");
require_once("site/framework/mysqli/src/loader.php"); // sql_driver
$sql = new mysqli_controler();
$status = $sql->RawSQL("installer/install.sql",true);
if($status["status"] == true)
{
    $avatar = new avatar();
    if($avatar->load(1) == true)
    {
        if($avatar->get_avatar_uid() == "system")
        {
            $sql->sqlSave(true);
            ?>
            <a href="setup"><button class="btn btn-primary btn-block" type="button">Setup</button></a>
            <?php
        }
        else
        {
            echo "Error: Expected install config db value is invaild";
        }
    }
    else
    {
        echo "Error: reading from datatabase";
    }
}
else
{
    echo "Rrror: installing db file: ".$status["message"]."";
}
?>
