<?php

$treevender = new treevender();
$input = new inputFilter();
$name = $input->postFilter("name");
$failed_on = "";
if (strlen($name) < 5) {
    $failed_on .= $lang["tree.cr.error.1"];
} elseif (strlen($name) > 100) {
    $failed_on .= $lang["tree.cr.error.2"];
} elseif ($treevender->loadByField("name", $name) == true) {
    $failed_on .= $lang["tree.cr.error.3"];
}
$status = false;
if ($failed_on == "") {
    $treevender = new treevender();
    $treevender->set_name($name);
    $create_status = $treevender->createEntry();
    if ($create_status["status"] == true) {
        $status = true;
        $this->output->setSwapTagString("redirect", "tree");
        $this->output->setSwapTagString("message", $lang["tree.cr.info.1"]);
    } else {
        $this->output->setSwapTagString("message", sprintf($lang["tree.cr.error.4"], $create_status["message"]));
    }
} else {
    $this->output->setSwapTagString("message", $failed_on);
}
