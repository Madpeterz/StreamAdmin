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
        $accept = $this->post("accept")->asString();
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
        $rental = $stream->relatedRental()->getFirst();
        $transaction_set = $stream->relatedTransactions();
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
