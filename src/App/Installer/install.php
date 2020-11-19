<?php

if (defined("correct") == false) {
    die("Error");
}
include "../shared/config/db.php";
require_once("shared/framework/mysqli/src/loader.php"); // sql_driver
$sql = new mysqli_controler();
$status = $sql->RawSQL("installer/install.sql", true);
if ($status["status"] == true) {
    $avatar = new avatar();
    if ($avatar->loadID(1) == true) {
        if ($avatar->get_avatar_uid() == "system") {
            $sql->sqlSave(true);
            $this->output->setSwapTagString("page_content", '<a href="setup"><button class="btn btn-primary btn-block" type="button">Setup</button></a>');
        } else {
            $this->output->setSwapTagString("page_content", 'Error: Expected install config db value is invaild');
        }
    } else {
        $this->output->setSwapTagString("page_content", 'Error: reading from datatabase');
    }
} else {
    $this->output->setSwapTagString("page_content", 'Error: installing db file: ' . $status["message"]);
}
