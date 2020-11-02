<?php

if (defined("correct") == false) {
    die("Error");
}
include "shared/config/load.php";
require_once("shared/framework/mysqli/src/loader.php"); // sql_driver
$sql = new mysqli_controler();
$slconfig = new slconfig();
if ($slconfig->load(1) == true) {
    file_put_contents("ready.txt", "ready");
    $view_reply->set_swap_tag_string("page_content", "Setup finished<br/> SL link code: " . $slconfig->get_sllinkcode() . "<br/>if you are running in docker please set: INSTALL_OK to 1");
    $view_reply->add_swap_tag_string("page_content", '<a href="[[url_base]]"><button class="btn btn-primary btn-block" type="button">Goto login</button></a>');
} else {
    die("Somthing went wrong - please contact support");
}
