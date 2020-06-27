<?php
if(defined("correct") == false) {die("Error");}
include("site/config/load.php");
require_once("site/vendor/yetonemorephpframework/mysqli/loader.php"); // sql_driver
$sql = new mysqli_controler();
$slconfig = new slconfig();
if($slconfig->load(1) == true)
{
    file_put_contents("ready.txt","ready");
    echo "Setup finished<br/> SL link code: ".$slconfig->get_sllinkcode()."<br/>if you are running in docker please set: INSTALL_OK to 1";
    ?>
    <a href="<?php echo $template_parts["url_base"];?>"><button class="btn btn-primary btn-block" type="button">Goto login</button></a>
    <?php
}
else
{
    echo "Somthing went wrong - please contact support";
}
?>
