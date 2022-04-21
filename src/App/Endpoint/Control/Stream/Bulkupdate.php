<?php

namespace App\Endpoint\Control\Stream;

use App\Models\Sets\StreamSet;
use App\Template\ControlAjax;

class BulkUpdate extends ControlAjax
{
    public function process(): void
    {
        $whereconfig = [
            "fields" => ["needWork","rentalLink"],
            "values" => [1,null],
            "types" => ["i","i"],
            "matches" => ["=","IS"],
        ];
        $stream_set = new StreamSet();
        $stream_set->loadWithConfig($whereconfig);
        $this->setSwapTag("redirect", "stream/bulkupdate");

        $streams_updated = 0;
        $streams_skipped_passwordChecks = 0;
        foreach ($stream_set->getAllIds() as $stream_id) {
            $stream = $stream_set->getObjectByID($stream_id);
            $accept = $this->input->post("stream" . $stream->getStreamUid() . "")->asString();
            if ($accept != "update") {
                continue;
            }
            $newadminpw = $this->input->post('stream' . $stream->getStreamUid() . 'adminpw')->asString();
            $newdjpw = $this->input->post('stream' . $stream->getStreamUid() . 'djpw')->asString();
            $allowAdminPassword = true;
            if ($stream->getAdminPassword() == $newadminpw) {
                $allowAdminPassword = false;
            }
            if (($newadminpw == "none") || ($newadminpw == "N/A")) {
                $allowAdminPassword = true;
            }
            if ($stream->getDjPassword() == $newdjpw) {
                $streams_skipped_passwordChecks++;
                continue;
            }
            if ($allowAdminPassword == false) {
                $streams_skipped_passwordChecks++;
                continue;
            }
            $stream->setAdminPassword($newadminpw);
            $stream->setDjPassword($newdjpw);
            $stream->setNeedWork(0);
            $update_status = $stream->updateEntry();
            if ($update_status->status == false) {
                $this->failed(sprintf(
                    "Unable to update stream %1\$s",
                    $update_status->message
                ));
                return;
            }
            $streams_updated++;
        }
        $this->ok(
            sprintf(
                "%1\$s streams updated",
                $streams_updated
            )
        );
        if ($streams_skipped_passwordChecks > 0) {
            $this->ok(
                sprintf(
                    "%1\$s streams updated %2\$s skipped for passwords not being updated",
                    $streams_updated,
                    $streams_skipped_passwordChecks
                )
            );
        }
    }
}
