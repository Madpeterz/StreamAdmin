<?php

$whereconfig = [
    "fields" => ["needwork","rentallink"],
    "values" => [1,null],
    "types" => ["i","i"],
    "matches" => ["=","IS"],
];
$stream_set = new stream_set();
$stream_set->loadWithConfig($whereconfig);
$status = true;
$this->output->setSwapTagString("redirect", "stream/bulkupdate");
$input = new inputFilter();
$streams_updated = 0;
$streams_skipped_original_adminusername = 0;
foreach ($stream_set->getAllIds() as $stream_id) {
    $stream = $stream_set->getObjectByID($stream_id);
    if ($stream->getOriginal_adminusername() == $stream->getAdminusername()) {
        $accept = $input->postFilter("stream" . $stream->getStream_uid() . "");
        if ($accept == "update") {
            $newadminpw = $input->postFilter('stream' . $stream->getStream_uid() . 'adminpw');
            $newdjpw = $input->postFilter('stream' . $stream->getStream_uid() . 'djpw');
            if (($stream->getAdminpassword() != $newadminpw) && ($stream->getDjpassword() != $newdjpw)) {
                $stream->setAdminpassword($newadminpw);
                $stream->setDjpassword($newdjpw);
                $stream->setNeedwork(0);
                $update_status = $stream->updateEntry();
                if ($update_status["status"] == false) {
                    echo sprintf($lang["stream.bu.error.1"], $update_status["message"]);
                    $status = false;
                    break;
                } else {
                    $streams_updated++;
                }
            }
        }
    } else {
        $streams_skipped_original_adminusername++;
    }
}
if ($status == true) {
    if ($streams_skipped_original_adminusername > 0) {
        $this->output->setSwapTagString("message", sprintf($lang["stream.bu.info.1"], $streams_updated, $streams_skipped_original_adminusername));
    } else {
        $this->output->setSwapTagString("message", sprintf($lang["stream.bu.info.1"], $streams_updated));
    }
}
