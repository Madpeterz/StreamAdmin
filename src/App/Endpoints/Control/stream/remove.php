<?php

$input = new inputFilter();
$accept = $input->postFilter("accept");
$this->output->setSwapTagString("redirect", "stream");
$status = false;
if ($accept == "Accept") {
    $stream = new stream();
    if ($stream->loadByField("stream_uid", $this->page) == true) {
        $transaction_set = new transactions_set();
        $load_status = $transaction_set->loadOnField("streamlink", $stream->getId());
        if ($load_status["status"] == true) {
            $unlink_ok = true;
            $bulkupdate_status = ["status" => false,"message" => "not run"];
            if ($transaction_set->getCount() > 0) {
                $unlink_ok = false;
                $bulkupdate_status = $transaction_set->update_single_field_for_collection("streamlink", null);
                if ($bulkupdate_status["status"] == true) {
                    $unlink_ok = true;
                }
            }
            if ($unlink_ok == true) {
                $remove_status = $streamremoveEntry();
                if ($remove_status["status"] == true) {
                    $status = true;
                    $this->output->setSwapTagString("message", $lang["stream.rm.info.1"]);
                } else {
                    $this->output->setSwapTagString("message", sprintf($lang["stream.rm.error.3"], $remove_status["message"]));
                }
            } else {
                $this->output->setSwapTagString("message", sprintf($lang["stream.rm.error.5"], $bulkupdate_status["message"]));
            }
        } else {
            $this->output->setSwapTagString("message", sprintf($lang["stream.rm.error.4"], $load_status["message"]));
        }
    } else {
        $this->output->setSwapTagString("message", $lang["stream.rm.error.2"]);
    }
} else {
    $status = false;
    $this->output->setSwapTagString("message", $lang["stream.rm.error.1"]);
    $this->output->setSwapTagString("redirect", "stream/manage/" . $this->page . "");
}
