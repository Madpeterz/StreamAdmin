<?php

$input = new inputFilter();
$accept = $input->postFilter("accept");
$this->output->setSwapTagString("redirect", "tree");
$status = false;
if ($accept == "Accept") {
    $treevender_packages = new treevender_packages();
    if ($treevender_packages->loadID($this->page) == true) {
        $redirect_to = $treevender_packages->getTreevenderlink();
        $remove_status = $treevender_packagesremoveEntry();
        if ($remove_status["status"] == true) {
            $status = true;
            $this->output->setSwapTagString("redirect", "tree/manage/" . $redirect_to . "");
            $this->output->setSwapTagString("message", $lang["tree.rp.info.1"]);
        } else {
            $this->output->setSwapTagString("message", sprintf($lang["tree.rp.error.3"], $remove_status["message"]));
        }
    } else {
        $this->output->setSwapTagString("message", $lang["tree.rp.error.1"]);
    }
} else {
    $this->output->setSwapTagString("message", $lang["tree.rp.error.1"]);
}
