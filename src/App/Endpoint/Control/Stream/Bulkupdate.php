<?php

namespace App\Endpoints\Control\Stream;

use App\Models\StreamSet;
use App\Template\ViewAjax;
use YAPF\InputFilter\InputFilter;

class Bulkupdate extends ViewAjax
{
    public function process(): void
    {
        $whereconfig = [
            "fields" => ["needwork","rentallink"],
            "values" => [1,null],
            "types" => ["i","i"],
            "matches" => ["=","IS"],
        ];
        $stream_set = new StreamSet();
        $stream_set->loadWithConfig($whereconfig);
        $status = true;
        $this->setSwapTag("redirect", "stream/bulkupdate");
        $input = new InputFilter();
        $streams_updated = 0;
        $streams_skipped_original_adminusername = 0;
        foreach ($stream_set->getAllIds() as $stream_id) {
            $stream = $stream_set->getObjectByID($stream_id);
            if ($stream->getOriginal_adminusername() == $stream->getAdminusername()) {
                $streams_skipped_original_adminusername++;
                continue;
            }
            $accept = $input->postFilter("stream" . $stream->getStream_uid() . "");
            if ($accept != "update") {
                continue;
            }
            $newadminpw = $input->postFilter('stream' . $stream->getStream_uid() . 'adminpw');
            $newdjpw = $input->postFilter('stream' . $stream->getStream_uid() . 'djpw');
            if (($stream->getAdminpassword() == $newadminpw) || ($stream->getDjpassword() == $newdjpw)) {
                continue;
            }
            $stream->setAdminpassword($newadminpw);
            $stream->setDjpassword($newdjpw);
            $stream->setNeedwork(0);
            $update_status = $stream->updateEntry();
            if ($update_status["status"] == false) {
                $this->setSwapTag(
                    "message",
                    sprintf(
                        "Unable to update stream %1\$s",
                        $update_status["message"]
                    )
                );
                $status = false;
                break;
            }
            $streams_updated++;
        }
        if ($status == false) {
            return;
        }
        $this->setSwapTag("status", "true");
        if ($streams_skipped_original_adminusername > 0) {
            $this->setSwapTag(
                "message",
                sprintf(
                    "%1\$s streams updated and %2\$s skipped due to admin username not matching",
                    $streams_updated,
                    $streams_skipped_original_adminusername
                )
            );
            return;
        }
        $this->setSwapTag(
            "message",
            sprintf(
                "%1\$s streams updated",
                $streams_updated
            )
        );
    }
}
