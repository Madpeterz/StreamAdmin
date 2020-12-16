<?php

$input = new inputFilter();
$accept = $input->postFilter("accept");
$this->output->setSwapTagString("redirect", "tree");
$status = false;
if ($accept == "Accept") {
    $treevender = new treevender();
    if ($treevender->loadID($this->page) == true) {
        $treevender_package_set = new treevender_packages_set();
        $treevender_package_set->loadOnField("treevenderlink", $treevender->getId());
        $purge_status = $treevender_package_set->purge_collection_set();
        if ($purge_status["status"] == true) {
            $remove_status = $treevenderremoveEntry();
            if ($remove_status["status"] == true) {
                $status = true;
                $this->output->setSwapTagString("message", $lang["tree.rm.info.1"]);
            } else {
                $this->output->setSwapTagString("message", sprintf($lang["tree.rm.error.4"], $remove_status["message"]));
            }
        } else {
            $this->output->setSwapTagString("message", $lang["tree.rm.error.3"]);
        }
    } else {
        $this->output->setSwapTagString("message", $lang["tree.rm.error.2"]);
    }
} else {
    $this->output->setSwapTagString("redirect", "tree/manage/" . $this->page . "");
    $this->output->setSwapTagString("message", $lang["tree.rm.error.1"]);
}
