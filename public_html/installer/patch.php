<?php
if(defined("correct") == false) {die("Error");}
include("site/config/db.php");
require_once("site/vendor/yetonemorephpframework/mysqli/loader.php"); // sql_driver
$sql = new mysqli_controler();
$status = $sql->RawSQL("installer/patch.sql",true);
if($status["status"] == true)
{
    $sql->sqlSave(true);
    ?>
    <a href="setup"><button class="btn btn-primary btn-block" type="button">Continue setup</button></a>
    <?php
}
else
{
    $sql->sqlRollBack(true);
    echo "Unable to install patch";
}
?>
