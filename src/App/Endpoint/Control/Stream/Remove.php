<?php

namespace App\Endpoint\Control\Stream;

use App\Models\Rental;
use App\Models\Stream;
use App\Models\Sets\ApirequestsSet;
use App\Models\Sets\TransactionsSet;
use App\Framework\ViewAjax;

class Remove extends ViewAjax
{
    public function process(): void
    {

        $accept = $this->input->post("accept");
        if ($accept != "Accept") {
            $this->failed("Did not Accept");
            $this->setSwapTag("redirect", "stream/manage/" . $this->siteConfig->getPage() . "");
            return;
        }
        $stream = new Stream();
        if ($stream->loadByStreamUid($this->siteConfig->getPage()) == false) {
            $this->failed("Unable to find stream");
            return;
        }
        $rental = new Rental();
        $rental->loadByStreamLink($stream->getId());
        if ($rental->getId() > 0) {
            $this->failed("That stream is currently rented how did you even get here.");
            return;
        }
        $api_requests = new ApirequestsSet();
        if ($api_requests->loadByStreamLink($stream->getId()) == false) {
            $this->failed("Unable to check for pending api requests attached to the stream");
            return;
        }
        if ($api_requests->getCount() > 0) {
            $this->failed(sprintf(
                "There are %1\$s pending api requests attached to the stream",
                $api_requests->getCount()
            ));
            return;
        }

        $transaction_set = new TransactionsSet();
        $load_status = $transaction_set->loadByStreamLink($stream->getId());
        if ($load_status["status"] == false) {
            $this->failed(
                sprintf("Unable to load transactions linked to stream because: %1\$s", $load_status["message"])
            );
            return;
        }
        if ($transaction_set->getCount() > 0) {
            $bulkupdate_status = $transaction_set->updateFieldInCollection("streamLink", null);
            if ($bulkupdate_status["status"] == false) {
                $this->failed(
                    sprintf("Unable to unlink transactions from stream because: %1\$s", $bulkupdate_status["message"])
                );
                return;
            }
        }

        $remove_status = $stream->removeEntry();
        if ($remove_status["status"] == false) {
            $this->failed(sprintf("Unable to remove stream: %1\$s", $remove_status["message"]));
            return;
        }
        $this->setSwapTag("redirect", "stream");
        $this->ok("Stream removed");
    }
}
