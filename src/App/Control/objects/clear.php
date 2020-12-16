<?php

$input = new inputFilter();
$accept = $input->postFilter("accept");
$this->output->setSwapTagString("redirect", "objects");
$status = false;
if ($accept == "Accept") {
    $objects_set = new objects_set();
    $objects_set->loadAll();
    $purge_status = $objects_set->purge_collection_set();
    if ($purge_status["status"] == true) {
        $status = true;
        $this->output->setSwapTagString("message", $lang["objects.cl.info.1"]);
    } else {
        $this->output->setSwapTagString("message", sprintf($lang["objects.cl.error.2"], $purge_status["message"]));
    }
} else {
    $this->output->setSwapTagString("message", $lang["objects.cl.error.1"]);
}
