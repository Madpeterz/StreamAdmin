<?php

namespace App\Endpoint\Control\Stream;

use App\R7\Set\StreamSet;
use App\Template\ViewAjax;
use YAPF\InputFilter\InputFilter;

class Bulkupdate extends ViewAjax
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
        $input = new InputFilter();
        $streams_updated = 0;
        $streams_skipped_originalAdminUsername = 0;
        foreach ($stream_set->getAllIds() as $stream_id) {
            $stream = $stream_set->getObjectByID($stream_id);
            if ($stream->getOriginalAdminUsername() != $stream->getAdminUsername()) {
                $streams_skipped_originalAdminUsername++;
                continue;
            }
            $accept = $input->postString("stream" . $stream->getStreamUid() . "");
            if ($accept != "update") {
                continue;
            }
            $newadminpw = $input->postString('stream' . $stream->getStreamUid() . 'adminpw');
            $newdjpw = $input->postString('stream' . $stream->getStreamUid() . 'djpw');
            if (($stream->getAdminPassword() == $newadminpw) || ($stream->getDjPassword() == $newdjpw)) {
                continue;
            }
            $stream->setAdminPassword($newadminpw);
            $stream->setDjPassword($newdjpw);
            $stream->setNeedWork(0);
            $update_status = $stream->updateEntry();
            if ($update_status["status"] == false) {
                $this->failed(sprintf(
                    "Unable to update stream %1\$s",
                    $update_status["message"]
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
        if ($streams_skipped_originalAdminUsername > 0) {
            $this->ok(
                sprintf(
                    "%1\$s streams updated and %2\$s skipped due to admin username not matching",
                    $streams_updated,
                    $streams_skipped_originalAdminUsername
                )
            );
        }
    }
}
