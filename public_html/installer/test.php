<?php
if(defined("correct") == false) {die("Error");}
include("site/config/db.php");
require_once("site/vendor/yetonemorephpframework/mysqli/loader.php"); // sql_driver
$sql = new mysqli_controler();
if($sql->sqlStart() == true)
{
    ?>
    <a href="install"><button class="btn btn-primary btn-block" type="button">Install</button></a>
    <br/>
    <a href="setup"><button class="btn btn-warning btn-block" type="button">Skip install - Goto setup</button></a>
    <?php
}
else
{
    ?>
    <a href=""><button class="btn btn-primary btn-block" type="button">Error unable to connect</button></a>
    <?php
}
?>