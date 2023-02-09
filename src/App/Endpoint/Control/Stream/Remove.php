<?php

namespace App\Endpoint\Control\Stream;

use App\Models\Stream;
use App\Template\ControlAjax;

class Remove extends ControlAjax
{
    public function process(): void
    {
        $accept = $this->input->post("accept")->asString();
        if ($accept != "Accept") {
            $this->failed("Did not Accept");
            $this->setSwapTag("redirect", "stream/manage/" . $this->siteConfig->getPage() . "");
            return;
        }
        $stream = new Stream();
        if ($stream->loadByStreamUid($this->siteConfig->getPage())->status == false) {
            $this->failed("Unable to find stream");
            return;
        }
        $transaction_set = $stream->relatedTransactions();
        if ($transaction_set->getCount() > 0) {
            $bulkupdate_status = $transaction_set->updateFieldInCollection("streamLink", null);
            if ($bulkupdate_status->status == false) {
                $this->failed(
                    sprintf("Unable to unlink transactions from stream because: %1\$s", $bulkupdate_status->message)
                );
                return;
            }
        }
        $streamid = $stream->getStreamUid();
        $port = $stream->getPort();
        $server = $stream->relatedServer()->getFirst();
        $stream->getPort();
        $remove_status = $stream->removeEntry();
        if ($remove_status->status == false) {
            $this->failed(sprintf("Unable to remove stream: %1\$s", $remove_status->message));
            return;
        }
        $this->redirectWithMessage("Stream removed");
        $this->createAuditLog($streamid, "---", $port, $server->getDomain());
    }
}
