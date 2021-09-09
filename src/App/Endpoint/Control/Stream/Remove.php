<?php

namespace App\Endpoint\Control\Stream;

use App\R7\Model\Stream;
use App\R7\Set\TransactionsSet;
use App\Template\ViewAjax;
use YAPF\InputFilter\InputFilter;

class Remove extends ViewAjax
{
    public function process(): void
    {
        $input = new InputFilter();
        $accept = $input->postString("accept");
        $this->setSwapTag("redirect", "stream");
        if ($accept != "Accept") {
            $this->failed("Did not Accept");
            $this->setSwapTag("redirect", "stream/manage/" . $this->page . "");
            return;
        }
        $stream = new Stream();
        if ($stream->loadByStreamUid($this->page) == false) {
            $this->failed("Unable to find stream");
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
        $this->ok("Stream removed");
    }
}
