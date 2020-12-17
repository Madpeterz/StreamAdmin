<?php

$input = new inputFilter();
$accept = $input->postFilter("accept");
$status = false;
$this->output->setSwapTagString("redirect", "package");
if ($accept == "Accept") {
    $package = new package();
    if ($package->loadByField("package_uid", $this->page) == true) {
        $stream_set = new stream_set();
        $load_status = $stream_set->loadOnField("packagelink", $package->getId());
        if ($load_status["status"] == true) {
            if ($stream_set->getCount() == 0) {
                $transaction_set = new transactions_set();
                $load_status = $transaction_set->loadOnField("packagelink", $package->getId());
                if ($load_status["status"] == true) {
                    if ($transaction_set->getCount() == 0) {
                        $rental_set = new rental_set();
                        $load_status = $rental_set->loadOnField("packagelink", $package->getId());
                        if ($load_status["status"] == true) {
                            if ($rental_set->getCount() == 0) {
                                $treevender_packages_set = new treevender_packages_set();
                                $load_status = $treevender_packages_set->loadOnField("packagelink", $package->getId());
                                if ($load_status["status"] == true) {
                                    if ($treevender_packages_set->getCount() == 0) {
                                        $remove_status = $packageremoveEntry();
                                        if ($remove_status["status"] == true) {
                                            $status = true;
                                            $this->output->setSwapTagString("message", $lang["package.rm.info.1"]);
                                        } else {
                                            $this->output->setSwapTagString("message", sprintf($lang["package.rm.error.3"], $remove_status["message"]));
                                        }
                                    } else {
                                        $this->output->setSwapTagString("message", sprintf($lang["package.rm.error.11"], $treevender_packages_set->getCount()));
                                    }
                                } else {
                                    $this->output->setSwapTagString("message", $lang["package.rm.error.10"]);
                                }
                            } else {
                                $this->output->setSwapTagString("message", sprintf($lang["package.rm.error.9"], $rental_set->getCount()));
                            }
                        } else {
                            $this->output->setSwapTagString("message", $lang["package.rm.error.8"]);
                        }
                    } else {
                        $this->output->setSwapTagString("message", sprintf($lang["package.rm.error.7"], $transaction_set->getCount()));
                    }
                } else {
                    $this->output->setSwapTagString("message", $lang["package.rm.error.6"]);
                }
            } else {
                $this->output->setSwapTagString("message", sprintf($lang["package.rm.error.5"], $stream_set->getCount()));
            }
        } else {
            $this->output->setSwapTagString("message", $lang["package.rm.error.4"]);
        }
    } else {
        $this->output->setSwapTagString("message", $lang["package.rm.error.2"]);
    }
} else {
    $this->output->setSwapTagString("message", $lang["package.rm.error.1"]);
    $this->output->setSwapTagString("redirect", "package/manage/" . $this->page . "");
}
