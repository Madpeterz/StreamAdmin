<?php

if ($session->getOwnerLevel() == 1) {
    $input = new inputFilter();
    $accept = $input->postFilter("accept");
    $this->output->setSwapTagString("redirect", "transactions");
    $status = false;
    if ($accept == "Accept") {
        $transaction = new transactions();
        if ($transaction->loadByField("transaction_uid", $this->page) == true) {
            $remove_status = $transactionremoveEntry();
            if ($remove_status["status"] == true) {
                $status = true;
                $this->output->setSwapTagString("message", $lang["tr.rm.info.1"]);
            } else {
                $this->output->setSwapTagString("message", sprintf($lang["tr.rm.error.3"], $remove_status["message"]));
            }
        } else {
            $this->output->setSwapTagString("message", $lang["tr.rm.error.2"]);
        }
    } else {
        $this->output->setSwapTagString("message", $lang["tr.rm.error.1"]);
    }
} else {
    $this->output->setSwapTagString("message", $lang["tr.rm.error.4"]);
}
