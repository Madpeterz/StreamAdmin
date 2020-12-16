<?php

$input = new inputFilter();
$package_id = $input->postFilter("package", "integer");

$status = false;
$treevender = new treevender();
$this->output->setSwapTagString("redirect", "tree");
if ($treevender->loadID($this->page) == true) {
    if ($package_id > 0) {
        $package = new package();
        if ($package->loadID($package_id) == true) {
            $treevender_package = new treevender_packages();
            $where_fields = [
                "fields" => ["packagelink","treevenderlink"],
                "values" => [$package->getId(),$treevender->getId()],
                "types" => ["i","i"],
                "matches" => ["=","="],
            ];
            if ($treevender_package->loadWithConfig($where_fields) == false) {
                $treevender_package = new treevender_packages();
                $treevender_package->setPackagelink($package->getId());
                $treevender_package->set_treevenderlink($treevender->getId());
                $create_status = $treevender_package->createEntry();
                if ($create_status["status"] == true) {
                    $this->output->setSwapTagString("redirect", "tree/manage/" . $this->page . "");
                    $this->output->setSwapTagString("message", $lang["tree.ap.info.1"]);
                    $status = true;
                } else {
                    $this->output->setSwapTagString("message", $lang["tree.ap.error.5"]);
                }
            } else {
                $this->output->setSwapTagString("message", $lang["tree.ap.error.4"]);
                $this->output->setSwapTagString("redirect", "");
            }
        } else {
            $this->output->setSwapTagString("message", $lang["tree.ap.error.3"]);
        }
    } else {
        $this->output->setSwapTagString("message", $lang["tree.ap.error.2"]);
    }
} else {
    $this->output->setSwapTagString("message", $lang["tree.ap.error.1"]);
}
