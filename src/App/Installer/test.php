<?php

if (defined("correct") == false) {
    die("Error");
}
include "../shared/config/db.php";
if (class_exists("db") == true) {
    require_once("shared/framework/mysqli/src/loader.php"); // sql_driver
    $sql = new mysqli_controler();
    if ($sql->sqlStart() == true) {
        $this->output->addSwapTagString("page_content", '
        <a href="install"><button class="btn btn-success btn-block" type="button">Install</button></a>
        <br/><br/><br/><hr/><p>Do not use this option unless told to!</p>
        <a href="setup"><button class="btn btn-warning btn-block" type="button">Skip install - Goto setup</button></a>
        ');
    } else {
        $this->output->addSwapTagString("page_content", '
        <a href=""><button class="btn btn-primary btn-block" type="button">Error unable to connect</button></a>
        ');
    }
} else {
    $this->output->addSwapTagString("page_content", '
    <a href=""><button class="btn btn-primary btn-block" type="button">Failed to find DB config!</button></a>
    ');
}
