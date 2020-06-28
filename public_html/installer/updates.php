<?php
if(defined("correct") == false) {die("Error");}
include("site/config/db.php");
require_once("site/vendor/yetonemorephpframework/mysqli/loader.php"); // sql_driver
$sql = new mysqli_controler();
$slconfig = new slconfig();
$slconfig->load(1);
$has_updates = true;
$all_ok = true;
while($has_updates == true)
{
    $has_updates = false;
    if(file_exists("versions/sql/".$slconfig->get_db_version().".sql") == true)
    {
        $has_updates = true;
        render();
        $status = $sql->RawSQL("versions/sql/".$slconfig->get_db_version().".sql",true);
        if($status["status"] == true)
        {
            $slconfig = new slconfig();
            $slconfig->load(1);
            $modal_folder = "site/model";
            $update_modal_folder = "versions/modal/".$slconfig->get_db_version()."";
            if(file_exists($update_modal_folder."/required.txt") == true)
            {
                $scanned_directory = array_diff(scandir($update_modal_folder), array('..', '.','required.txt'));
                foreach($scanned_directory as $entry)
                {
                    unlink($modal_folder."/".$entry);
                    copy($update_modal_folder."/".$entry,$modal_folder."/".$entry);
                    echo "Updated file: ".$entry."<br/>";
                }
            }
            echo "<br/>Update apply now running version ".$slconfig->get_db_version()."<hr/>";
        }
        else
        {
            $all_ok = false;
            echo "Issue detected: <hr/>".$status["message"]." please contact support";
            break;
        }
    }
}
if($all_ok == true)
{
    $sql->sqlSave(true);
    ?>
    <a href="final"><button class="btn btn-primary btn-block" type="button">goto final screen</button></a>
    <?php
}
?>
